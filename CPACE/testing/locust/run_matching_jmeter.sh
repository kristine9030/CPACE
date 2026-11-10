#!/bin/bash
export PATH="$PATH:/c/Users/acer/AppData/Roaming/Python/Python314/Scripts"
cd "C:/xampp/htdocs/CPACE/CPACE/testing/locust"
for users in 10 50 100 200; do
  spawn=$(python -c "print(round($users/30, 2))")
  echo "=== Running users=$users spawn-rate=$spawn ==="
  locust -f cpace_load_test.py --headless \
    --host "http://localhost/CPACE/CPACE/public" \
    --users $users --spawn-rate $spawn --run-time 300s \
    --csv "results_local_apache/run-$users" \
    --student-email "loadtest.student@cpace.test" --student-password "LoadTest123!" \
    > "results_local_apache/run-$users.log" 2>&1
  echo "=== Done users=$users ==="
done
echo "ALL_RUNS_COMPLETE"
