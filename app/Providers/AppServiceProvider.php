<?php

namespace App\Providers;

use App\Services\Polyline;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Polyline::class, function ($app) {
            return new \App\Services\Polyline\HereMaps\Api();
        });
    }
}
