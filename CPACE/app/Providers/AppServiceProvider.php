<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Azure\Provider as MicrosoftAzureProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('azure', MicrosoftAzureProvider::class);
        });

        View::composer([
            'partials.sidebar',
            'partials.student-mobile-header',
            'partials.student-bottom-nav',
            'partials.faculty-sidebar',
            'partials.chair-sidebar',
            'partials.topbar-actions',
        ], function ($view) {
            $view->with('unreadNotifications', Auth::check()
                ? DB::table('notifications')->where('recipient_id', Auth::id())->where('is_read', false)->count()
                : 0);
        });
    }
}
