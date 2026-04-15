#!/usr/bin/env python3
"""
WordPress Plugin & Theme Version Checker
-----------------------------------------
Reads plugins.yml and checks each entry against:
  1. WordPress.org API — flags outdated versions (always runs, no auth)
  2. Wordfence Intelligence API — flags known vulnerabilities (optional, needs WORDFENCE_API_TOKEN)

Exit codes:
  0 = all clear
  1 = vulnerabilities found or script error
  2 = outdated plugins found (warnings only)
"""

import json
import os
import sys
import urllib.request
import urllib.error
from pathlib import Path

# PyYAML may need to be installed in CI
import yaml


# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def load_manifest(path: str) -> dict:
    with open(path) as f:
        return yaml.safe_load(f)


def compare_versions(installed: str, latest: str) -> int:
    """Return -1 if installed < latest, 0 if equal, 1 if installed > latest."""
    def _parts(v):
        return [int(x) for x in v.split(".") if x.isdigit()]
    a, b = _parts(installed), _parts(latest)
    # Pad to same length
    length = max(len(a), len(b))
    a += [0] * (length - len(a))
    b += [0] * (length - len(b))
    for x, y in zip(a, b):
        if x < y:
            return -1
        if x > y:
            return 1
    return 0


def version_in_range(version: str, from_ver: str, from_inc: bool, to_ver: str, to_inc: bool) -> bool:
    """Check if a version falls within an affected range."""
    parts = lambda v: [int(x) for x in v.split(".") if x.isdigit()]
    v = parts(version)
    lo = parts(from_ver) if from_ver != "*" else [0]
    hi = parts(to_ver) if to_ver != "*" else [999999]
    length = max(len(v), len(lo), len(hi))
    v += [0] * (length - len(v))
    lo += [0] * (length - len(lo))
    hi += [0] * (length - len(hi))

    above_lo = v > lo if not from_inc else v >= lo
    below_hi = v < hi if not to_inc else v <= hi
    return above_lo and below_hi


# ---------------------------------------------------------------------------
# WordPress.org API — check for updates (no auth needed)
# ---------------------------------------------------------------------------

def check_wporg_plugin(slug: str) -> dict | None:
    """Query WordPress.org for the latest version of a plugin."""
    url = f"https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&slug={slug}"
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "CVRemote-CI/1.0"})
        with urllib.request.urlopen(req, timeout=15) as resp:
            data = json.loads(resp.read())
            if isinstance(data, dict) and "version" in data:
                return {"latest": data["version"], "name": data.get("name", slug)}
    except (urllib.error.URLError, json.JSONDecodeError, KeyError):
        pass
    return None


def check_wporg_theme(slug: str) -> dict | None:
    """Query WordPress.org for the latest version of a theme."""
    url = f"https://api.wordpress.org/themes/info/1.2/?action=theme_information&slug={slug}"
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "CVRemote-CI/1.0"})
        with urllib.request.urlopen(req, timeout=15) as resp:
            data = json.loads(resp.read())
            if isinstance(data, dict) and "version" in data:
                return {"latest": data["version"], "name": data.get("name", slug)}
    except (urllib.error.URLError, json.JSONDecodeError, KeyError):
        pass
    return None


# ---------------------------------------------------------------------------
# Wordfence Intelligence API — check for known vulnerabilities (optional)
# ---------------------------------------------------------------------------

def fetch_wordfence_vulns(api_token: str) -> list:
    """Fetch the full Wordfence vulnerability production feed."""
    url = "https://www.wordfence.com/api/intelligence/v3/vulnerabilities/production"
    req = urllib.request.Request(url, headers={
        "Authorization": f"Bearer {api_token}",
        "User-Agent": "CVRemote-CI/1.0",
    })
    try:
        with urllib.request.urlopen(req, timeout=60) as resp:
            data = json.loads(resp.read())
            return list(data.values()) if isinstance(data, dict) else data
    except (urllib.error.URLError, json.JSONDecodeError) as e:
        print(f"  ⚠  Wordfence API error: {e}")
        return []


def check_wordfence_vulns(slug: str, version: str, sw_type: str, vulns: list) -> list:
    """Check if a specific plugin/theme version has known vulnerabilities."""
    hits = []
    for vuln in vulns:
        for sw in vuln.get("software", []):
            if sw.get("type") != sw_type or sw.get("slug") != slug:
                continue
            for _range_key, range_info in sw.get("affected_versions", {}).items():
                from_ver = range_info.get("from_version", "*")
                to_ver = range_info.get("to_version", "*")
                from_inc = range_info.get("from_inclusive", True)
                to_inc = range_info.get("to_inclusive", True)
                if version_in_range(version, from_ver, from_inc, to_ver, to_inc):
                    hits.append({
                        "title": vuln.get("title", "Unknown"),
                        "cve": vuln.get("cve", "N/A"),
                        "cvss_score": vuln.get("cvss", {}).get("score", "N/A"),
                        "cvss_rating": vuln.get("cvss", {}).get("rating", "N/A"),
                        "patched": sw.get("patched", False),
                        "patched_versions": sw.get("patched_versions", []),
                    })
    return hits


# ---------------------------------------------------------------------------
# Reporting
# ---------------------------------------------------------------------------

def print_github_annotation(level: str, message: str, file: str = "plugins.yml"):
    """Emit a GitHub Actions annotation."""
    print(f"::{level} file={file}::{message}")


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def main():
    manifest_path = os.environ.get("MANIFEST_PATH", "plugins.yml")
    wordfence_token = os.environ.get("WORDFENCE_API_TOKEN", "").strip()

    if not Path(manifest_path).exists():
        print(f"❌ Manifest not found: {manifest_path}")
        sys.exit(1)

    manifest = load_manifest(manifest_path)
    plugins = manifest.get("plugins", {}) or {}
    themes = manifest.get("themes", {}) or {}

    outdated = []
    vulnerable = []
    checked = 0

    # --- Load Wordfence data if token is available ---
    wf_vulns = []
    if wordfence_token:
        print("🔒 Wordfence API token found — checking for known vulnerabilities...")
        wf_vulns = fetch_wordfence_vulns(wordfence_token)
        print(f"   Loaded {len(wf_vulns)} vulnerability records.\n")
    else:
        print("ℹ️  No WORDFENCE_API_TOKEN set — skipping vulnerability database check.")
        print("   To enable, add a Wordfence Intelligence API key as a GitHub secret.\n")

    # --- Check plugins ---
    print("=" * 60)
    print("PLUGIN VERSION CHECK")
    print("=" * 60)

    for slug, info in plugins.items():
        name = info.get("name", slug)
        version = str(info.get("version", "0"))
        checked += 1

        print(f"\n📦 {name} (v{version})")

        # WordPress.org update check
        wporg = check_wporg_plugin(slug)
        if wporg:
            latest = wporg["latest"]
            cmp = compare_versions(version, latest)
            if cmp < 0:
                print(f"   ⚠️  OUTDATED — latest is v{latest}")
                print_github_annotation("warning", f"{name}: installed v{version}, latest v{latest}")
                outdated.append(f"{name}: v{version} → v{latest}")
            elif cmp == 0:
                print(f"   ✅ Up to date (v{latest})")
            else:
                print(f"   ✅ Ahead of wp.org (v{latest})")
        else:
            print(f"   ℹ️  Not on WordPress.org (premium/private plugin)")

        # Wordfence vulnerability check
        if wf_vulns:
            hits = check_wordfence_vulns(slug, version, "plugin", wf_vulns)
            if hits:
                for h in hits:
                    sev = f"CVSS {h['cvss_score']} ({h['cvss_rating']})" if h['cvss_score'] != 'N/A' else ""
                    fix = f"→ update to {', '.join(h['patched_versions'])}" if h['patched_versions'] else "→ no patch available"
                    print(f"   🚨 VULN: {h['title']} [{h['cve']}] {sev} {fix}")
                    print_github_annotation("error", f"{name} v{version}: {h['title']} [{h['cve']}] {fix}")
                    vulnerable.append(f"{name}: {h['title']} [{h['cve']}]")
            else:
                print(f"   🔒 No known vulnerabilities")

    # --- Check themes ---
    print(f"\n{'=' * 60}")
    print("THEME VERSION CHECK")
    print("=" * 60)

    for slug, info in themes.items():
        name = info.get("name", slug)
        version = str(info.get("version", "0"))
        checked += 1

        print(f"\n🎨 {name} (v{version})")

        wporg = check_wporg_theme(slug)
        if wporg:
            latest = wporg["latest"]
            cmp = compare_versions(version, latest)
            if cmp < 0:
                print(f"   ⚠️  OUTDATED — latest is v{latest}")
                print_github_annotation("warning", f"{name}: installed v{version}, latest v{latest}")
                outdated.append(f"{name}: v{version} → v{latest}")
            elif cmp == 0:
                print(f"   ✅ Up to date (v{latest})")
            else:
                print(f"   ✅ Ahead of wp.org (v{latest})")
        else:
            print(f"   ℹ️  Not on WordPress.org")

        if wf_vulns:
            hits = check_wordfence_vulns(slug, version, "theme", wf_vulns)
            if hits:
                for h in hits:
                    sev = f"CVSS {h['cvss_score']} ({h['cvss_rating']})" if h['cvss_score'] != 'N/A' else ""
                    fix = f"→ update to {', '.join(h['patched_versions'])}" if h['patched_versions'] else "→ no patch available"
                    print(f"   🚨 VULN: {h['title']} [{h['cve']}] {sev} {fix}")
                    print_github_annotation("error", f"{name} v{version}: {h['title']} [{h['cve']}] {fix}")
                    vulnerable.append(f"{name}: {h['title']} [{h['cve']}]")
            else:
                print(f"   🔒 No known vulnerabilities")

    # --- Summary ---
    print(f"\n{'=' * 60}")
    print("SUMMARY")
    print(f"{'=' * 60}")
    print(f"Checked: {checked} packages")
    print(f"Outdated: {len(outdated)}")
    print(f"Vulnerable: {len(vulnerable)}")

    if outdated:
        print(f"\n⚠️  Outdated packages:")
        for o in outdated:
            print(f"   • {o}")

    if vulnerable:
        print(f"\n🚨 Vulnerable packages:")
        for v in vulnerable:
            print(f"   • {v}")
        print(f"\n❌ FAILED — {len(vulnerable)} known vulnerabilities found.")
        sys.exit(1)

    if outdated:
        print(f"\n⚠️  {len(outdated)} packages need updates. Consider updating soon.")
        sys.exit(2)

    print("\n✅ All packages are up to date with no known vulnerabilities.")
    sys.exit(0)


if __name__ == "__main__":
    main()
