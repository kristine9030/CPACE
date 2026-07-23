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

        // Share the unread notification + message counts with every view so the
        // top-bar bell and messages icons work globally (computed once per request).
        View::composer('*', function ($view) {
            static $counts = null;

            if ($counts === null) {
                if (Auth::check()) {
                    $uid = Auth::id();

                    $counts = [
                        'unreadNotifications' => DB::table('notifications')
                            ->where('recipient_id', $uid)
                            ->where('is_read', false)
                            ->count(),
                        'unreadMessages' => DB::table('messages as m')
                            ->join('conversation_participants as cp', function ($join) use ($uid) {
                                $join->on('cp.conversation_id', '=', 'm.conversation_id')
                                     ->where('cp.user_id', '=', $uid);
                            })
                            ->where('m.sender_id', '!=', $uid)
                            ->where(function ($q) {
                                $q->whereNull('cp.last_read_at')
                                  ->orWhereColumn('m.created_at', '>', 'cp.last_read_at');
                            })
                            ->count(),
                    ];
                } else {
                    $counts = ['unreadNotifications' => 0, 'unreadMessages' => 0];
                }
            }

            $view->with($counts);
        });
    }
}
