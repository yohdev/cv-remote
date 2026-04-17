#!/usr/bin/env python3
"""
Lighthouse Audit via PageSpeed Insights API
---------------------------------------------
Reads lighthouse-pages.yml and runs a PageSpeed Insights audit
for each page. Outputs a summary table and GitHub annotations.

Requires: PAGESPEED_API_KEY environment variable

Exit codes:
  0 = all pages meet thresholds
  1 = script error
  2 = one or more pages below thresholds (warning)
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
# PageSpeed Insights API
# ---------------------------------------------------------------------------

PSI_API = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed"

CATEGORIES = ["performance", "accessibility", "best-practices", "seo"]

# Map config keys to API category names
CATEGORY_MAP = {
    "performance": "performance",
    "accessibility": "accessibility",
    "best_practices": "best-practices",
    "seo": "seo",
}


def run_psi_audit(url: str, api_key: str, strategy: str = "mobile") -> dict | None:
    """Run a PageSpeed Insights audit for a single URL."""
    params = urllib.parse.urlencode({
        "url": url,
        "key": api_key,
        "strategy": strategy,
        "category": CATEGORIES,
    }, doseq=True)

    request_url = f"{PSI_API}?{params}"
    req = urllib.request.Request(request_url, headers={"User-Agent": "CVRemote-CI/1.0"})

    try:
        with urllib.request.urlopen(req, timeout=120) as resp:
            return json.loads(resp.read())
    except urllib.error.HTTPError as e:
        body = e.read().decode() if e.fp else ""
        print(f"   ❌ API error {e.code}: {body[:200]}")
        return None
    except urllib.error.URLError as e:
        print(f"   ❌ Network error: {e}")
        return None


def extract_scores(result: dict) -> dict:
    """Extract category scores from PSI response."""
    scores = {}
    categories = result.get("lighthouseResult", {}).get("categories", {})
    for cat_id, cat_data in categories.items():
        score = cat_data.get("score")
        if score is not None:
            scores[cat_id] = round(score * 100)
    return scores


def extract_key_metrics(result: dict) -> dict:
    """Extract Core Web Vitals and key metrics."""
    audits = result.get("lighthouseResult", {}).get("audits", {})
    metrics = {}

    metric_keys = {
        "first-contentful-paint": "FCP",
        "largest-contentful-paint": "LCP",
        "total-blocking-time": "TBT",
        "cumulative-layout-shift": "CLS",
        "speed-index": "Speed Index",
        "interactive": "TTI",
    }

    for audit_key, label in metric_keys.items():
        audit = audits.get(audit_key, {})
        display = audit.get("displayValue", "N/A")
        metrics[label] = display

    return metrics


# ---------------------------------------------------------------------------
# Reporting
# ---------------------------------------------------------------------------

def score_emoji(score: int, threshold: int) -> str:
    if threshold == 0:
        return "⚪"
    if score >= 90:
        return "🟢"
    elif score >= threshold:
        return "🟡"
    else:
        return "🔴"


def print_github_annotation(level: str, message: str, file: str = "lighthouse-pages.yml"):
    print(f"::{level} file={file}::{message}")


def generate_markdown_summary(results: list, thresholds: dict) -> str:
    """Generate a markdown summary table for GitHub Actions job summary."""
    lines = []
    lines.append("## Lighthouse Audit Results")
    lines.append("")
    lines.append("| Page | Performance | Accessibility | Best Practices | SEO | LCP | CLS |")
    lines.append("|------|:-----------:|:------------:|:--------------:|:---:|:---:|:---:|")

    for r in results:
        scores = r["scores"]
        metrics = r["metrics"]
        perf = scores.get("performance", "—")
        a11y = scores.get("accessibility", "—")
        bp = scores.get("best-practices", "—")
        seo = scores.get("seo", "—")
        lcp = metrics.get("LCP", "—")
        cls_ = metrics.get("CLS", "—")

        perf_e = score_emoji(perf, thresholds.get("performance", 0)) if isinstance(perf, int) else ""
        a11y_e = score_emoji(a11y, thresholds.get("accessibility", 0)) if isinstance(a11y, int) else ""
        bp_e = score_emoji(bp, thresholds.get("best_practices", 0)) if isinstance(bp, int) else ""
        seo_e = score_emoji(seo, thresholds.get("seo", 0)) if isinstance(seo, int) else ""

        lines.append(f"| {r['name']} | {perf_e} {perf} | {a11y_e} {a11y} | {bp_e} {bp} | {seo_e} {seo} | {lcp} | {cls_} |")

    lines.append("")
    lines.append(f"*Strategy: mobile | Thresholds: Perf≥{thresholds.get('performance', 0)}, "
                 f"A11y≥{thresholds.get('accessibility', 0)}, "
                 f"BP≥{thresholds.get('best_practices', 0)}, "
                 f"SEO≥{thresholds.get('seo', 0)}*")
    return "\n".join(lines)


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def main():
    config_path = os.environ.get("LIGHTHOUSE_CONFIG", "lighthouse-pages.yml")
    api_key = os.environ.get("PAGESPEED_API_KEY", "").strip()

    if not api_key:
        print("❌ PAGESPEED_API_KEY not set. Add it as a GitHub secret.")
        sys.exit(1)

    if not os.path.exists(config_path):
        print(f"❌ Config not found: {config_path}")
        sys.exit(1)

    with open(config_path) as f:
        config = yaml.safe_load(f)

    base_url = config.get("base_url", "").rstrip("/")
    pages = config.get("pages", [])
    thresholds = config.get("thresholds", {})

    if not base_url:
        print("❌ No base_url defined in config.")
        sys.exit(1)

    if not pages:
        print("❌ No pages defined in config.")
        sys.exit(1)

    print(f"🔍 Lighthouse audit for: {base_url}")
    print(f"📄 Pages to scan: {len(pages)}")
    print(f"📊 Thresholds: {thresholds}\n")

    all_results = []
    below_threshold = []

    for i, page in enumerate(pages):
        path = page.get("path", "/")
        name = page.get("name", path)
        url = f"{base_url}{path}"

        print(f"{'=' * 60}")
        print(f"[{i+1}/{len(pages)}] {name}: {url}")
        print(f"{'=' * 60}")

        result = run_psi_audit(url, api_key)

        if not result:
            print(f"   ⚠️  Skipped (API error)\n")
            all_results.append({
                "name": name,
                "url": url,
                "scores": {},
                "metrics": {},
                "error": True,
            })
            continue

        scores = extract_scores(result)
        metrics = extract_key_metrics(result)

        all_results.append({
            "name": name,
            "url": url,
            "scores": scores,
            "metrics": metrics,
            "error": False,
        })

        # Print scores
        for cat_key, threshold_key in [("performance", "performance"),
                                        ("accessibility", "accessibility"),
                                        ("best-practices", "best_practices"),
                                        ("seo", "seo")]:
            score = scores.get(cat_key, "N/A")
            threshold = thresholds.get(threshold_key, 0)
            if isinstance(score, int):
                emoji = score_emoji(score, threshold)
                status = ""
                if threshold > 0 and score < threshold:
                    status = f" ⚠️  BELOW THRESHOLD ({threshold})"
                    below_threshold.append(f"{name} — {cat_key}: {score} (threshold: {threshold})")
                    print_github_annotation("warning",
                        f"{name}: {cat_key} score {score} is below threshold {threshold}")
                print(f"   {emoji} {cat_key.replace('-', ' ').title()}: {score}{status}")

        # Print key metrics
        print(f"\n   Core Web Vitals:")
        for label, value in metrics.items():
            print(f"     {label}: {value}")

        print()

        # Rate limiting: PSI allows ~25 requests/100 seconds on free tier
        if i < len(pages) - 1:
            time.sleep(3)

    # --- Summary ---
    print(f"\n{'=' * 60}")
    print("SUMMARY")
    print(f"{'=' * 60}")
    print(f"Pages audited: {len(all_results)}")
    errors = sum(1 for r in all_results if r.get("error"))
    if errors:
        print(f"Errors: {errors}")

    # Write GitHub Actions job summary
    summary_file = os.environ.get("GITHUB_STEP_SUMMARY", "")
    if summary_file:
        md = generate_markdown_summary(all_results, thresholds)
        with open(summary_file, "a") as f:
            f.write(md + "\n")
        print("📋 Results written to job summary.")

    # Write JSON results for downstream use (e.g., report generation)
    json_output = os.environ.get("LIGHTHOUSE_OUTPUT", "lighthouse-results.json")
    with open(json_output, "w") as f:
        json.dump({
            "base_url": base_url,
            "timestamp": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
            "thresholds": thresholds,
            "results": all_results,
        }, f, indent=2)
    print(f"💾 Full results saved to {json_output}")

    if below_threshold:
        print(f"\n⚠️  Pages below threshold:")
        for item in below_threshold:
            print(f"   • {item}")
        print(f"\n⚠️  {len(below_threshold)} scores below threshold.")
        sys.exit(2)

    print("\n✅ All pages meet score thresholds.")
    sys.exit(0)


if __name__ == "__main__":
    main()
