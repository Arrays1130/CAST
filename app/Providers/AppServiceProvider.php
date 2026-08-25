<?php

namespace App\Providers;

use App\Mail\GoogleAppsScriptTransport;
use App\Models\Paper;
use App\Policies\PaperPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Mail::extend('google_apps_script', function () {
            return new GoogleAppsScriptTransport(
                (string) config('services.google_apps_script.url'),
                (string) config('services.google_apps_script.secret'),
            );
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            if ($root = config('app.url')) {
                URL::forceRootUrl($root);
            }
        }

        Gate::policy(Paper::class, PaperPolicy::class);

        View::composer('layouts.app', function ($view) {
            $view->with(
                'unreadNotices',
                auth()->user()?->notices()->whereNull('read_at')->count() ?? 0
            );
        });
    }
}
