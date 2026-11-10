<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('crm-login', function (Request $request) {
            $ip = $request->ip();

            return [
                Limit::perMinute(20)->by('ip:'.$ip),
                Limit::perMinute(5)->by('login:'.$ip.':'.hash('sha256', strtolower($request->string('email')->substr(0, 255)->toString()))),
            ];
        });
    }
}
