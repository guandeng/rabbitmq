<?php

namespace Guandeng\Rabbitmq\Broker;

use Guandeng\Rabbitmq\Message\Message;

class Publisher extends Broker
{
    public function __construct()
    {
        parent::__construct();  
    }

    /**
     * 生产消息
     */
    public function publish($messages)
    {
        foreach ($this->binds as $bind) {
            $this->queueDeclareBind($bind['queue'], $bind['routing_key'] ?? null, $this->exchange);
            foreach ($messages as $message) {
                $this->batch_basic_publish(
                    (new Message($message))->getAMQPMessage(), $this->exchange, $bind['routing_key'] ?? null
                );
            }
            $this->publish_batch();
        }
    }
}
