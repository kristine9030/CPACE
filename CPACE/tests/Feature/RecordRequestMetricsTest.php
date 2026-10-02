<?php

namespace Tests\Feature;

use App\Http\Middleware\RecordRequestMetrics;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * RecordRequestMetrics feeds the Super Admin Performance page's avg/p95/p99
 * response-time cards from a rolling sample of request durations. Routes
 * that synchronously call an external AI provider (ai-tutor chat, test-bank
 * ai-draft, curriculum gap-fill generate) wait seconds on that provider,
 * which dragged p95/p99 up to look like the whole app was slow rather than
 * a few AI calls. Confirms those routes are still counted as requests, but
 * their durations land in a separate "ai_*" bucket (its own Performance page
 * card) instead of the app's own response-time samples.
 */
class RecordRequestMetricsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    private function handle(?string $routeName): void
    {
        $request = Request::create('/whatever', 'GET');
        $route = new Route('GET', '/whatever', []);
        if ($routeName) {
            $route->name($routeName);
        }
        $request->setRouteResolver(fn () => $route);

        (new RecordRequestMetrics())->handle($request, fn () => new Response('ok', 200));
    }

    public function test_ordinary_route_is_counted_in_duration_samples(): void
    {
        $today = now()->format('Y-m-d');

        $this->handle('dashboard');

        $this->assertCount(1, Cache::get('metrics.durations', []));
        $this->assertSame(1, Cache::get("metrics.duration_count.{$today}"));
        $this->assertSame(1, Cache::get("metrics.requests.{$today}"));
    }

    public function test_ai_route_goes_to_the_ai_bucket_instead_of_the_app_bucket(): void
    {
        $today = now()->format('Y-m-d');

        $this->handle('ai-tutor.chat');

        $this->assertCount(0, Cache::get('metrics.durations', []));
        $this->assertSame(0, (int) Cache::get("metrics.duration_count.{$today}", 0));
        $this->assertCount(1, Cache::get('metrics.ai_durations', []));
        $this->assertSame(1, Cache::get("metrics.ai_duration_count.{$today}"));
        $this->assertSame(1, Cache::get("metrics.requests.{$today}"));
    }

    public function test_every_known_ai_route_lands_in_the_ai_bucket(): void
    {
        $today = now()->format('Y-m-d');

        foreach ([
            'ai-tutor.chat',
            'ai-tutor.performance-insights',
            'faculty.question.ai-draft',
            'chair.ai-review.generate',
            'faculty.test-bank.ai-review.generate',
        ] as $routeName) {
            $this->handle($routeName);
        }

        $this->assertCount(0, Cache::get('metrics.durations', []));
        $this->assertCount(5, Cache::get('metrics.ai_durations', []));
        $this->assertSame(5, Cache::get("metrics.requests.{$today}"));
    }
}
