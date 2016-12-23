<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;


class ServiceProvider extends IlluminateServiceProvider 
{


    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /*
    * Register bindings in the container
    *
    * @return void
    */
    public function register()
    {
        $this->app->singleton(Helper::class, function($app) {
            return new Helper();
        });
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provider()
    {
        return [Helper::class];
    }

}