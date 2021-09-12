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
            __DIR__ . '/config/rabbitmq.php' => base_path('config/rabbitmq.php'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Guandeng\Rabbitmq\RabbitMQ', function ($app) {
            $config = $app['config']->get("rabbitmq-laravel");
            return new RabbitMQ($config);
        });
    }
}
