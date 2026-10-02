<?php

namespace App\Services;

/**
 * Best-effort summary extraction for uploaded test-run JSON. Every parser
 * here is defensive: an unrecognized shape returns ['recognized' => false]
 * rather than throwing, so the raw payload is always still viewable even
 * when the summary can't be computed. This is NOT a full JUnit/Playwright/
 * Newman schema implementation — it reads the handful of fields those tools
 * commonly emit and says so plainly when it can't find them.
 */
class TestReportParser
{
    public function summarize(string $type, array $payload): array
    {
        return match ($type) {
            'frontend' => $this->summarizePlaywright($payload),
            'api' => $this->summarizePostman($payload),
            'load' => $this->summarizeLoad($payload),
            'warehouse' => $this->summarizeWarehouse($payload),
            default => ['recognized' => false],
        };
    }

    /** Playwright's built-in JSON reporter (`--reporter=json`). */
    private function summarizePlaywright(array $payload): array
    {
        // Newer Playwright: top-level `stats`. Older/custom reporters vary,
        // so fall back to walking suites->specs->tests->results if absent.
        if (isset($payload['stats']) && is_array($payload['stats'])) {
            $stats = $payload['stats'];
            $passed = (int) ($stats['expected'] ?? 0);
            $failed = (int) ($stats['unexpected'] ?? 0);
            $skipped = (int) ($stats['skipped'] ?? 0);
            $flaky = (int) ($stats['flaky'] ?? 0);

            return [
                'recognized' => true,
                'passed' => $passed,
                'failed' => $failed,
                'skipped' => $skipped,
                'flaky' => $flaky,
                'total' => $passed + $failed + $skipped + $flaky,
                'duration_ms' => isset($stats['duration']) ? (int) $stats['duration'] : null,
            ];
        }

        if (isset($payload['suites']) && is_array($payload['suites'])) {
            $passed = $failed = $skipped = 0;
            $walk = function (array $suites) use (&$walk, &$passed, &$failed, &$skipped) {
                foreach ($suites as $suite) {
                    foreach (($suite['specs'] ?? []) as $spec) {
                        foreach (($spec['tests'] ?? []) as $test) {
                            foreach (($test['results'] ?? []) as $result) {
                                match ($result['status'] ?? null) {
                                    'passed' => $passed++,
                                    'skipped' => $skipped++,
                                    default => $failed++,
                                };
                            }
                        }
                    }
                    if (! empty($suite['suites'])) {
                        $walk($suite['suites']);
                    }
                }
            };
            $walk($payload['suites']);

            return [
                'recognized' => true,
                'passed' => $passed, 'failed' => $failed, 'skipped' => $skipped,
                'flaky' => 0, 'total' => $passed + $failed + $skipped, 'duration_ms' => null,
            ];
        }

        return ['recognized' => false];
    }

    /** Postman collection run exported from Newman (`newman run --reporters json`). */
    private function summarizePostman(array $payload): array
    {
        $run = $payload['run'] ?? null;
        if (! is_array($run) || ! isset($run['stats'])) {
            return ['recognized' => false];
        }

        $stats = $run['stats'];
        $requests = $stats['requests'] ?? [];
        $assertions = $stats['assertions'] ?? [];
        $timings = $run['timings'] ?? [];

        $started = $timings['started'] ?? null;
        $completed = $timings['completed'] ?? null;

        return [
            'recognized' => true,
            'requests_total' => (int) ($requests['total'] ?? 0),
            'requests_failed' => (int) ($requests['failed'] ?? 0),
            'assertions_total' => (int) ($assertions['total'] ?? 0),
            'assertions_failed' => (int) ($assertions['failed'] ?? 0),
            'duration_ms' => ($started !== null && $completed !== null) ? (int) ($completed - $started) : null,
        ];
    }

    /**
     * No single standard format exists across load-testing tools (k6,
     * Artillery, ab, Locust, ...). This reads a small, documented shape —
     * {requests, duration_ms, rps, p95_ms, error_rate, tool} — and the
     * upload page tells the admin to shape their tool's output into it
     * (or export it directly if the tool already emits these field names).
     */
    private function summarizeLoad(array $payload): array
    {
        $hasCoreFields = array_key_exists('requests', $payload) || array_key_exists('rps', $payload);
        if (! $hasCoreFields) {
            return ['recognized' => false];
        }

        return [
            'recognized' => true,
            'tool' => $payload['tool'] ?? null,
            'requests' => isset($payload['requests']) ? (int) $payload['requests'] : null,
            'duration_ms' => isset($payload['duration_ms']) ? (int) $payload['duration_ms'] : null,
            'rps' => isset($payload['rps']) ? round((float) $payload['rps'], 2) : null,
            'p95_ms' => isset($payload['p95_ms']) ? (int) $payload['p95_ms'] : null,
            'error_rate' => isset($payload['error_rate']) ? round((float) $payload['error_rate'], 2) : null,
        ];
    }

    /**
     * Same reasoning as the load benchmark: a documented, tool-agnostic
     * shape — {queries: [{query, rows, duration_ms}], summary?: {...}}.
     */
    private function summarizeWarehouse(array $payload): array
    {
        $queries = $payload['queries'] ?? null;
        if (! is_array($queries) || empty($queries)) {
            return ['recognized' => false];
        }

        $durations = array_map(fn ($q) => (float) ($q['duration_ms'] ?? 0), $queries);
        $slowestIndex = array_keys($durations, max($durations))[0] ?? 0;

        return [
            'recognized' => true,
            'query_count' => count($queries),
            'total_duration_ms' => (int) array_sum($durations),
            'avg_duration_ms' => count($durations) > 0 ? (int) round(array_sum($durations) / count($durations)) : null,
            'slowest_query' => $queries[$slowestIndex]['query'] ?? null,
            'slowest_ms' => (int) ($durations[$slowestIndex] ?? 0),
        ];
    }
}
