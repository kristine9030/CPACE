"""
CPAce Load Test (Locust) - mirrors testing/jmeter/cpace-load-test.jmx

Same scenario, same endpoints, same think-time, so results are comparable
side-by-side with the JMeter runs (Chapter 4 Tables XIII-XVI).

Flow per simulated student:
  1. Once per user: GET /login -> extract CSRF token -> POST /login -> assert
     the response did not land back on /login (login succeeded).
  2. Then repeatedly, with a think-time delay before each request:
     GET /dashboard, GET /calendar, GET /adaptive-quizzes

Usage (headless, matching a JMeter run of N users / R ramp-up / D duration):
  locust -f cpace_load_test.py --headless \
      --host https://staging.cpace.site \
      --users N --spawn-rate $(python -c "print(N/R)") --run-time Ds \
      --csv results/run-N \
      --student-email you@example.com --student-password secret

  e.g. to match "jmeter -Jusers=50 -Jrampup=30 -Jduration=300":
  locust -f cpace_load_test.py --headless --host https://staging.cpace.site \
      --users 50 --spawn-rate 1.67 --run-time 300s \
      --csv results/run-50 \
      --student-email you@example.com --student-password secret

Think-time (JMeter's ${__P(thinktime,1000)}, default 1000ms) is configurable
with --think-time (milliseconds), same default.
"""

import re
import time

from locust import HttpUser, between, events, task

CSRF_RE = re.compile(r'name="_token"\s+value="([^"]+)"')


@events.init_command_line_parser.add_listener
def _add_custom_args(parser):
    parser.add_argument("--student-email", type=str, env_var="STUDENT_EMAIL", default="", help="Login email (matches -Jstudent_email)")
    parser.add_argument("--student-password", type=str, env_var="STUDENT_PASSWORD", default="", help="Login password (matches -Jstudent_password)")
    parser.add_argument("--think-time", type=int, env_var="THINK_TIME_MS", default=1000, help="Think-time in ms before each page request (matches -Jthinktime, default 1000)")
    parser.add_argument("--path-prefix", type=str, env_var="PATH_PREFIX", default="", help="Path prefix, matches -Jpath_prefix")


class CPAceStudent(HttpUser):
    # JMeter's ConstantTimer applies a fixed delay; between() here is overridden
    # per-request via time.sleep(think_time) to match that exactly instead of
    # Locust's usual random wait-time-between-tasks behavior.
    wait_time = between(0, 0)

    def on_start(self):
        prefix = self.environment.parsed_options.path_prefix
        email = self.environment.parsed_options.student_email
        password = self.environment.parsed_options.student_password
        self.think_time_s = self.environment.parsed_options.think_time / 1000.0
        self.prefix = prefix

        with self.client.get(f"{prefix}/login", name="GET Login Page", catch_response=True) as resp:
            match = CSRF_RE.search(resp.text)
            csrf_token = match.group(1) if match else "NOT_FOUND"

        with self.client.post(
            f"{prefix}/login",
            data={"_token": csrf_token, "email": email, "password": password},
            name="POST Authenticate",
            catch_response=True,
        ) as resp:
            if "/login" in resp.url or "/login" in (resp.text or "")[:2000]:
                resp.failure("Login Succeeded (not back on /login) assertion failed")
            else:
                resp.success()

    @task
    def browse(self):
        time.sleep(self.think_time_s)
        self.client.get(f"{self.prefix}/dashboard", name="Dashboard")

        time.sleep(self.think_time_s)
        self.client.get(f"{self.prefix}/calendar", name="Spaced Repetition Calendar")

        time.sleep(self.think_time_s)
        self.client.get(f"{self.prefix}/adaptive-quizzes", name="Adaptive Quiz Engine")
