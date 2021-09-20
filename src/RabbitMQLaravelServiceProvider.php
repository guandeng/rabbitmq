<?php

namespace Guandeng\Rabbitmq;

use Illuminate\Support\ServiceProvider;

class RabbitMQLaravelServiceProvider extends ServiceProvider
{
     /**
     * @var array
     */
    protected $commands = [
        Console\ConsumerRabbitMQCommand::class,
        Console\PublisherRabbitMQCommand::class,
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/rabbitmq.php';
        if (function_exists('config_path')) {
            $publishPath = config_path('rabbitmq.php');
        } else {
            $publishPath = base_path('config/rabbitmq.php');
        }
        $this->publishes([$configPath => $publishPath], 'config');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
        $this->app->bind('rabbitmq', function ($app) {
            $config = $app['config']->get("rabbitmq");
            return new RabbitMQ($config);
        });
    }
}
