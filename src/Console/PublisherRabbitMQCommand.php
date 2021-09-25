<?php

namespace Guandeng\Rabbitmq\Console;

use Guandeng\Rabbitmq\Broker\Broker;
use Illuminate\Console\Command;

class PublisherRabbitMQCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publisher:rabbitmq
                            {publisher}
                            {message}
                            {--max-priority=}
                            {--durable=1}
                            {--auto-delete=0}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送RabbitMQ消息';

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
        $this->info('开始发送RabbitMQ消息...');
        $publisher = $this->input->getArgument('publisher');
        $message   = $this->input->getArgument('message');
        if (!array_key_exists($publisher, config('rabbitmq.publishers'))) {
            $this->output->error('生产者不存在:' . $publisher);
            return -1;
        }
        $maxPriority = (int) $this->option('max-priority');
        if ($maxPriority) {
            $options['arguments']['x-max-priority'] = $maxPriority;
        }

        $options['durable']     = (bool) $this->option('durable');
        $options['auto-delete'] = (bool) $this->option('auto-delete');
        for ($i = 0; $i < 1000; $i++) {
            $msgs = [];
            array_push($msgs, $message);
            $rabbitmq->exchange(
                config('rabbitmq.publishers.' . $publisher), 
                $options)->publish($msgs);
        }
        $this->info(json_encode($msgs));
    }
}
