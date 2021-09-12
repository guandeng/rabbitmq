<?php

namespace Guandeng\Rabbitmq\Broker;

use Guandeng\Rabbitmq\Message\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Broker extends AMQPChannel
{
    public $config;
    public $connect;
    public $defaultRoutingKey;
    public $exchange;
    public $exchange_declare;
    public $queue_declare = [];

    public function __construct($config = [])
    {
        if (!empty($config)) {
            $this->setConfig($config);
        }

        $this->createConnet();

        parent::__construct($this->connect);
    }

    /**
     * 重新配置
     */
    public function setConfig($config = [])
    {
        foreach ($config as $key => $value) {
            $this->config[$key] = $value;
        }
    }

    /**
     * 队列连接配置
     */
    public function setQueueDeclareConfig($queue_declare = [])
    {
        $this->queue_declare = $queue_declare;
    }
    /**
     * 创建连接
     */
    public function createConnet()
    {
        //创建连接
        $this->connect = new AMQPStreamConnection(
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost'],
            $this->config['insist'],
            $this->config['login_method'],
            $this->config['locale'],
            $this->config['io'],
            $this->config['heartbeat'],
            $this->config['connection_timeout'],
            $this->config['channel_rpc_timeout'],
        );
    }

    /**
     * 设置路由键
     */
    public function setRouteKey($route_key)
    {
        $this->defaultRoutingKey = $route_key;
    }

    public function setExchange($exchange, $type = "direct")
    {
        $this->exchange = $exchange;
        $this->exchange_declare($exchange, $type, false, true, false);
    }

    /**
     * 生产消息（支持多条消息）
     */
    public function publishBatch($messages, $routingKey = null)
    {
        $this->queueDeclareBind($routingKey);
        // Create the messages
        foreach ($messages as $message) {
            $this->batch_basic_publish(
                (new Message($message))->getAMQPMessage(), $this->exchange, $routingKey
            );
        }
        $this->publish_batch();
    }

    /**
     * 声明队列
     * @param $routingKey
     */
    protected function queueDeclareBind(&$routingKey)
    {
        if (is_null($routingKey)) {
            $routingKey = $this->defaultRoutingKey;
        }

        // Create/declare queue
        $this->queue_declare(
            $routingKey,
            $this->queue_declare['passive'],
            $this->queue_declare['durable'],
            $this->queue_declare['exclusive'],
            $this->queue_declare['auto_delete']
        );

        if ($this->exchange != "") {
            // Bind the queue to the exchange
            $this->queue_bind($routingKey, $this->exchange, $routingKey);
        }
    }
}
