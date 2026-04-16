#!/usr/bin/env python3
"""
accessiBe accessScan — Accessibility Audit via accessFlow API
--------------------------------------------------------------
Reads lighthouse-pages.yml (shared config) and triggers an accessiBe
accessibility scan for each page via the accessFlow v3 API.

Requires: ACCESSIBE_API_TOKEN environment variable

Exit codes:
  0 = scan completed (results reported as warnings only)
  1 = script/configuration error
"""

import json
import os
import sys
import time
import urllib.request
import urllib.error
import urllib.parse

import yaml


# ---------------------------------------------------------------------------
# accessFlow API v3
# ---------------------------------------------------------------------------

ACCESSFLOW_BASE = "https://accessflow.accessibe.com/api/v3"

TRIGGER_PAGE_SCAN = f"{ACCESSFLOW_BASE}/trigger-page-scan"
PAGE_SCAN_STATUS = f"{ACCESSFLOW_BASE}/page-scan-status"
# If accessiBe provides a results endpoint, we'll use it
PAGE_SCAN_RESULTS = f"{ACCESSFLOW_BASE}/page-scan-results"

# Polling config
MAX_POLL_ATTEMPTS = 30       # Max polls before giving up
POLL_INTERVAL_SECONDS = 10   # Seconds between status checks
SCAN_TIMEOUT_SECONDS = 300   # Total timeout for a scan


def api_request(url: str, api_token: str, data: dict | None = None) -> dict | None:
    """Make a POST request to the accessFlow API."""
    payload = json.dumps(data).encode("utf-8") if data else None
    headers = {
        "Content-Type": "application/json",
        "Authorization": f"Bearer {api_token}",
        "User-Agent": "CVRemote-CI/1.0",
    }

    req = urllib.request.Request(url, data=payload, headers=headers, method="POST")

    try:
        with urllib.request.urlopen(req, timeout=60) as resp:
            body = resp.read().decode("utf-8")
            return json.loads(body) if body else {}
    except urllib.error.HTTPError as e:
        body = e.read().decode("utf-8", errors="replace") if e.fp else ""
        print(f"   ❌ API error {e.code}: {body[:500]}")
        return None
    except urllib.error.URLError as e:
        print(f"   ❌ Network error: {e}")
        return None
    except json.JSONDecodeError:
        print("   ❌ Could not parse API response as JSON")
        return None


def trigger_scan(api_token: str, domain: str, paths: list[str]) -> dict | None:
    """Trigger an accessiBe scan for specific pages."""
    data = {
        "domain": domain,
        "pages": paths,
    }
    print(f"   🔄 Triggering scan for {len(paths)} page(s) on {domain}...")
    return api_request(TRIGGER_PAGE_SCAN, api_token, data)


def check_scan_status(api_token: str, domain: str, webpage_ids: list = None) -> dict | None:
    """Check scan status for pages."""
    data = {"domain": domain}
    if webpage_ids:
        data["webpageIds"] = webpage_ids
    return api_request(PAGE_SCAN_STATUS, api_token, data)


def get_scan_results(api_token: str, domain: str, webpage_ids: list = None) -> dict | None:
    """Fetch scan results for pages."""
    data = {"domain": domain}
    if webpage_ids:
        data["webpageIds"] = webpage_ids
    return api_request(PAGE_SCAN_RESULTS, api_token, data)


def wait_for_scan(api_token: str, domain: str, webpage_ids: list = None) -> bool:
    """Poll until the scan completes or times out."""
    start = time.time()
    for attempt in range(1, MAX_POLL_ATTEMPTS + 1):
        elapsed = time.time() - start
        if elapsed > SCAN_TIMEOUT_SECONDS:
            print(f"   ⏰ Scan timed out after {int(elapsed)}s")
            return False

        print(f"   ⏳ Checking status (attempt {attempt}/{MAX_POLL_ATTEMPTS})...")
        status = check_scan_status(api_token, domain, webpage_ids)

        if status is None:
            print("   ⚠️  Could not retrieve status, retrying...")
            time.sleep(POLL_INTERVAL_SECONDS)
            continue

        # The API returns scanning=true while in progress
        # Adapt to whatever the actual response shape is
        if status.get("success") and not status.get("data", {}).get("scanning", True):
            print("   ✅ Scan complete!")
            return True

        # Alternative: check if all pages have completed
        pages_status = status.get("data", {}).get("pages", [])
        if pages_status and all(p.get("status") == "completed" for p in pages_status):
            print("   ✅ All pages scanned!")
            return True

        time.sleep(POLL_INTERVAL_SECONDS)

    print(f"   ⏰ Gave up after {MAX_POLL_ATTEMPTS} attempts")
    return False


# ---------------------------------------------------------------------------
# Reporting
# ---------------------------------------------------------------------------

def severity_emoji(level: str) -> str:
    level = level.lower()
    if level in ("critical", "serious"):
        return "🔴"
    elif level in ("moderate",):
        return "🟡"
    elif level in ("minor",):
        return "🟢"
    return "⚪"


def generate_markdown_summary(scan_data: dict, pages: list) -> str:
    """Generate a markdown summary for GitHub Actions job summary."""
    lines = []
    lines.append("## accessiBe Accessibility Scan Results")
    lines.append("")

    results = scan_data.get("data", {}).get("pages", scan_data.get("data", {}).get("results", []))

    if not results:
        lines.append("*Scan completed but no detailed page results were returned.*")
        lines.append("")
        lines.append("Check the [accessFlow dashboard](https://accessflow.accessibe.com) for full results.")
        return "\n".join(lines)

    # Summary table
    lines.append("| Page | Score | Issues | Critical | Serious | Moderate | Minor |")
    lines.append("|------|:-----:|:------:|:--------:|:-------:|:--------:|:-----:|")

    total_issues = 0
    for page_result in results:
        url = page_result.get("url", page_result.get("webpage_url", "Unknown"))
        path = url.replace(scan_data.get("domain", ""), "") or "/"

        # Try to match page name from config
        name = path
        for p in pages:
            if p.get("path", "").rstrip("/") == path.rstrip("/"):
                name = p.get("name", path)
                break

        score = page_result.get("score", page_result.get("accessibility_score", "—"))
        issues = page_result.get("issues", page_result.get("violations", []))
        issue_count = len(issues) if isinstance(issues, list) else issues

        # Count by severity
        critical = 0
        serious = 0
        moderate = 0
        minor = 0
        if isinstance(issues, list):
            for issue in issues:
                sev = issue.get("severity", issue.get("impact", "")).lower()
                if sev == "critical":
                    critical += 1
                elif sev == "serious":
                    serious += 1
                elif sev == "moderate":
                    moderate += 1
                elif sev == "minor":
                    minor += 1
            issue_count = len(issues)
            total_issues += issue_count

        score_display = f"{score}" if isinstance(score, (int, float)) else score
        lines.append(f"| {name} | {score_display} | {issue_count} | {critical} | {serious} | {moderate} | {minor} |")

    lines.append("")
    lines.append(f"*Total issues found: {total_issues} | Mode: warning only (non-blocking)*")
    lines.append("")
    lines.append("View detailed results in the [accessFlow dashboard](https://accessflow.accessibe.com)")

    return "\n".join(lines)


def print_page_issues(page_result: dict, page_name: str):
    """Print detailed issues for a single page."""
    issues = page_result.get("issues", page_result.get("violations", []))
    if not isinstance(issues, list) or not issues:
        print(f"   ℹ️  No detailed issue data available")
        return

    print(f"   Found {len(issues)} issue(s):")
    for issue in issues[:20]:  # Cap at 20 to avoid flooding logs
        sev = issue.get("severity", issue.get("impact", "unknown"))
        desc = issue.get("description", issue.get("message", issue.get("help", "No description")))
        wcag = issue.get("wcag", issue.get("criteria", ""))
        emoji = severity_emoji(sev)
        wcag_str = f" [{wcag}]" if wcag else ""
        print(f"      {emoji} [{sev.upper()}]{wcag_str} {desc[:120]}")

    if len(issues) > 20:
        print(f"      ... and {len(issues) - 20} more (see accessFlow dashboard)")


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def main():
    config_path = os.environ.get("ACCESSIBE_CONFIG", "lighthouse-pages.yml")
    api_token = os.environ.get("ACCESSIBE_API_TOKEN", "").strip()

    if not api_token:
        print("❌ ACCESSIBE_API_TOKEN not set. Add it as a GitHub secret.")
        sys.exit(1)

    if not os.path.exists(config_path):
        print(f"❌ Config not found: {config_path}")
        sys.exit(1)

    with open(config_path) as f:
        config = yaml.safe_load(f)

    base_url = config.get("base_url", "").rstrip("/")
    pages = config.get("pages", [])

    if not base_url:
        print("❌ No base_url defined in config.")
        sys.exit(1)

    if not pages:
        print("❌ No pages defined in config.")
        sys.exit(1)

    # Extract domain from base_url (e.g., "www.cvremotesolutions.com")
    domain = base_url.replace("https://", "").replace("http://", "").rstrip("/")
    paths = [p.get("path", "/") for p in pages]

    print(f"♿ accessiBe Accessibility Scan")
    print(f"🌐 Domain: {domain}")
    print(f"📄 Pages to scan: {len(pages)}")
    print(f"⚙️  Mode: Warning only (non-blocking)\n")

    # Step 1: Trigger the scan
    print(f"{'=' * 60}")
    print("STEP 1: Trigger Scan")
    print(f"{'=' * 60}")

    trigger_result = trigger_scan(api_token, domain, paths)

    if trigger_result is None:
        print("\n❌ Failed to trigger scan. Check your API token and domain.")
        print("   Token format: should be a valid accessFlow API token")
        print(f"   Domain used: {domain}")
        print("\n💡 Tip: Verify the token at https://accessflow.accessibe.com")
        sys.exit(1)

    print(f"   API Response: {json.dumps(trigger_result, indent=2)[:500]}")

    if not trigger_result.get("success", True):
        message = trigger_result.get("message", "Unknown error")
        print(f"\n❌ Scan trigger failed: {message}")
        sys.exit(1)

    # Extract webpage IDs if provided
    webpage_ids = trigger_result.get("data", {}).get("webpageIds", [])
    print(f"   Scan triggered successfully!")
    if webpage_ids:
        print(f"   Webpage IDs: {webpage_ids}")

    # Step 2: Wait for scan to complete
    print(f"\n{'=' * 60}")
    print("STEP 2: Wait for Scan Completion")
    print(f"{'=' * 60}")

    scan_completed = wait_for_scan(api_token, domain, webpage_ids)

    # Step 3: Retrieve and report results
    print(f"\n{'=' * 60}")
    print("STEP 3: Results")
    print(f"{'=' * 60}")

    scan_results = None
    if scan_completed:
        scan_results = get_scan_results(api_token, domain, webpage_ids)

    if scan_results and scan_results.get("data"):
        result_pages = scan_results.get("data", {}).get("pages",
                        scan_results.get("data", {}).get("results", []))

        if isinstance(result_pages, list):
            for page_result in result_pages:
                url = page_result.get("url", page_result.get("webpage_url", "Unknown"))
                path = url.replace(f"https://{domain}", "").replace(f"http://{domain}", "") or "/"

                # Match name from config
                name = path
                for p in pages:
                    if p.get("path", "").rstrip("/") == path.rstrip("/"):
                        name = p.get("name", path)
                        break

                score = page_result.get("score", page_result.get("accessibility_score", "N/A"))
                print(f"\n   📄 {name} ({path})")
                print(f"   Score: {score}")
                print_page_issues(page_result, name)

        # Write GitHub Actions job summary
        summary_file = os.environ.get("GITHUB_STEP_SUMMARY", "")
        if summary_file:
            md = generate_markdown_summary(scan_results, pages)
            with open(summary_file, "a") as f:
                f.write(md + "\n")
            print("\n📋 Results written to job summary.")
    else:
        print("\n⚠️  Could not retrieve detailed results.")
        print("   The scan was triggered but results may still be processing.")
        print("   Check https://accessflow.accessibe.com for full results.")

        # Still write a summary
        summary_file = os.environ.get("GITHUB_STEP_SUMMARY", "")
        if summary_file:
            lines = [
                "## accessiBe Accessibility Scan Results",
                "",
                "⚠️ Scan was triggered but detailed results were not available at pipeline time.",
                "",
                f"**Domain:** {domain}",
                f"**Pages scanned:** {len(pages)}",
                "",
                "View results in the [accessFlow dashboard](https://accessflow.accessibe.com)",
            ]
            with open(summary_file, "a") as f:
                f.write("\n".join(lines) + "\n")

    # Save raw results for downstream use (report generation pipeline)
    output_path = os.environ.get("ACCESSIBE_OUTPUT", "accessibe-results.json")
    output_data = {
        "domain": domain,
        "timestamp": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
        "trigger_response": trigger_result,
        "scan_completed": scan_completed,
        "results": scan_results,
        "pages_config": pages,
    }
    with open(output_path, "w") as f:
        json.dump(output_data, f, indent=2)
    print(f"\n💾 Raw results saved to {output_path}")

    # Always exit 0 — warning only mode
    print(f"\n{'=' * 60}")
    print("DONE — Warning only mode, pipeline will not fail")
    print(f"{'=' * 60}")

    # Emit GitHub annotations for visibility
    if scan_results and scan_results.get("data"):
        result_pages = scan_results.get("data", {}).get("pages",
                        scan_results.get("data", {}).get("results", []))
        if isinstance(result_pages, list):
            for page_result in result_pages:
                issues = page_result.get("issues", page_result.get("violations", []))
                if isinstance(issues, list) and issues:
                    url = page_result.get("url", "Unknown")
                    critical_serious = [i for i in issues
                                       if i.get("severity", i.get("impact", "")).lower()
                                       in ("critical", "serious")]
                    if critical_serious:
                        print(f"::warning file=lighthouse-pages.yml::"
                              f"accessiBe: {url} has {len(critical_serious)} "
                              f"critical/serious a11y issue(s)")

    sys.exit(0)


if __name__ == "__main__":
    main()
