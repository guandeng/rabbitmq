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
    protected $signature = 'consumer:rabbitmq {consumer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理异步rabbitmq消息';

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
    public function handle(Broker $rabbitmq)
    {
        $this->info('开始监听RabbitMQ消息...');
        $consumer = $this->input->getArgument('consumer');
        if (!array_key_exists($consumer, config('rabbitmq.consumers'))) {
            $this->output->error(':消费者不存在:'.$consumer);
            return -1;
        }
        $rabbitmq->queue(config('rabbitmq.consumers.'.$consumer))->listenToQueue();

        // foreach ($consumers as $consumer => $queue_info) {
        //     info($queue_info);
        //     $rabbitmq->queue($queue_info)->listenToQueue();
        // }
        // return $this;
    }
}
