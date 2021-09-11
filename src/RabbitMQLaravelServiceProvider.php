<?php

namespace Guandeng\Rabbitmq;

use Illuminate\Support\ServiceProvider;

class RabbitMQLaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
		    __DIR__.'/config.php' => base_path('config/rabbitmq-laravel.php'),
	    ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton("md5hash",function (){
            return new Md5Hasher();
        });
    }
}
