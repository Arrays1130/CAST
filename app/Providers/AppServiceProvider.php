<?php

namespace App\Providers;

use App\Models\Paper;
use App\Policies\PaperPolicy;
use Illuminate\Support\Facades\Gate;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
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
