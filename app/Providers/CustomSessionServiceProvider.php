<?php

namespace App\Providers;

use App\Extensions\CustomSessionManager;
use Illuminate\Session\SessionServiceProvider;

class CustomSessionServiceProvider extends SessionServiceProvider
{
    /**
     * Register the session manager instance.
     *
     * @return void
     */
    protected function registerSessionManager()
    {
        $this->app->singleton('session', function ($app) {
            return new CustomSessionManager($app);
        });
    }
}
