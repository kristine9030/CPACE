#!/usr/bin/env bash
# Runs a k6 load test against CPACE and uploads the result to the Super
# Admin "Test Reports" -> Load Benchmark tab.
#
# No load-testing tool ships with CPACE itself — this is a template for
# whichever one you use (k6 shown here since it has a clean JSON summary;
# Artillery/ab/Locust work the same way, just reshape their own output into
# the same {tool, requests, duration_ms, rps, p95_ms, error_rate} object
# before the curl upload at the bottom).
#
# Requires: k6 (https://k6.io/docs/get-started/installation/), jq, curl.
#
# Usage:
#   CPACE_APP_URL=https://cpace.site \
#   CPACE_TEST_REPORTS_TOKEN=xxxx \
#   ./scripts/run-load-benchmark.sh
#
# A minimal inline k6 script is used below (GETs the login page repeatedly).
# Point TARGET_PATH at a route that matters more for your case, and adjust
# VUS/DURATION for the load shape you actually want to measure.

set -euo pipefail

: "${CPACE_APP_URL:?Set CPACE_APP_URL, e.g. https://cpace.site}"
: "${CPACE_TEST_REPORTS_TOKEN:?Set CPACE_TEST_REPORTS_TOKEN — a named Super Admin token from /superadmin/api-tokens}"
TARGET_PATH="${TARGET_PATH:-/login}"
VUS="${VUS:-10}"
DURATION="${DURATION:-30s}"

WORKDIR=$(mktemp -d)
trap 'rm -rf "$WORKDIR"' EXIT

cat > "$WORKDIR/script.js" <<EOF
import http from 'k6/http';
export const options = { vus: ${VUS}, duration: '${DURATION}' };
export default function () {
  http.get('${CPACE_APP_URL}${TARGET_PATH}');
}
EOF

k6 run --summary-export="$WORKDIR/summary.json" "$WORKDIR/script.js"

PAYLOAD=$(jq -c --arg tool "k6" '{
  tool: $tool,
  requests: .metrics.http_reqs.count,
  duration_ms: (.state.testRunDurationMs // 0),
  rps: .metrics.http_reqs.rate,
  p95_ms: .metrics.http_req_duration["p(95)"],
  error_rate: ((.metrics.http_req_failed.rate // 0) * 100)
}' "$WORKDIR/summary.json")

curl -sf -X POST "${CPACE_APP_URL}/api/test-reports" \
  -H "Authorization: Bearer ${CPACE_TEST_REPORTS_TOKEN}" \
  -H "Content-Type: application/json" \
  -d "{\"type\":\"load\",\"title\":\"k6 load benchmark — $(date '+%Y-%m-%d %H:%M')\",\"payload\":${PAYLOAD}}"

echo "Uploaded."
