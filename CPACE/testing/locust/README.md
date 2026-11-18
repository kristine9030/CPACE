# CPAce Locust Load Test

Locust equivalent of [`../jmeter/cpace-load-test.jmx`](../jmeter/cpace-load-test.jmx) -
same login flow, same three endpoints (`/dashboard`, `/calendar`,
`/adaptive-quizzes`), same 1000ms think-time default - so results are
directly comparable to the existing JMeter runs for Chapter 4.

Install once: `pip install locust`

## Matching the existing JMeter runs

JMeter's `ramp_time` is "seconds to spawn all users"; Locust's `--spawn-rate`
is "users spawned per second," so `spawn-rate = users / rampup`. The runs
below match the JMeter `run-10` / `run-50` / `run-100` / `run-200` results
(30s ramp-up, 300s duration):

```bash
# run-10  (jmeter -Jusers=10  -Jrampup=30 -Jduration=300)
locust -f cpace_load_test.py --headless --host https://staging.cpace.site \
  --users 10 --spawn-rate 0.33 --run-time 300s --csv results/run-10 \
  --student-email YOU@EXAMPLE.COM --student-password SECRET

# run-50  (jmeter -Jusers=50  -Jrampup=30 -Jduration=300)
locust -f cpace_load_test.py --headless --host https://staging.cpace.site \
  --users 50 --spawn-rate 1.67 --run-time 300s --csv results/run-50 \
  --student-email YOU@EXAMPLE.COM --student-password SECRET

# run-100 (jmeter -Jusers=100 -Jrampup=30 -Jduration=300)
locust -f cpace_load_test.py --headless --host https://staging.cpace.site \
  --users 100 --spawn-rate 3.33 --run-time 300s --csv results/run-100 \
  --student-email YOU@EXAMPLE.COM --student-password SECRET

# run-200 (jmeter -Jusers=200 -Jrampup=30 -Jduration=300)
locust -f cpace_load_test.py --headless --host https://staging.cpace.site \
  --users 200 --spawn-rate 6.67 --run-time 300s --csv results/run-200 \
  --student-email YOU@EXAMPLE.COM --student-password SECRET
```

Flags you can override (defaults match the `.jmx`'s `__P(...)` defaults):
- `--think-time` - ms delay before each page request (default `1000`, matches `-Jthinktime`)
- `--path-prefix` - URL path prefix (default none, matches `-Jpath_prefix`)
- `--student-email` / `--student-password` - matches `-Jstudent_email` / `-Jstudent_password`

## Output

`--csv results/run-N` produces `run-N_stats.csv`, `run-N_stats_history.csv`,
and `run-N_failures.csv` - use `run-N_stats.csv`'s per-request rows (`Dashboard`,
`Spaced Repetition Calendar`, `Adaptive Quiz Engine`) to line up against the
JMeter Summary Report's rows of the same names for the comparison table.

## Local run against XAMPP

```bash
locust -f cpace_load_test.py --headless --host http://localhost \
  --users 10 --spawn-rate 0.33 --run-time 60s --csv results_local/run-10 \
  --student-email YOU@EXAMPLE.COM --student-password SECRET
```
