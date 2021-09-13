<?php

namespace Guandeng\Rabbitmq\Console;

use Guandeng\Rabbitmq\Broker\Broker;
use Illuminate\Console\Command;

class ConsumerRabbitMQCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'consumer:rabbitmq';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理异步rabbit消息';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->log('开始监听消息...');
        $handlers = ["\\App\\QueueHandlers\\MyHandler"];
        \RabbitMQ::listenToQueue($handlers,'test');
        return $this;
    }

    private function log($msg = '')
    {
        if (!$msg) {
            return $this;
        }
        if (php_sapi_name() == 'cli') {
            echo $msg, PHP_EOL;
        }
        info('rabbit_consumer', [$msg]);
        return $this;
    }
}
