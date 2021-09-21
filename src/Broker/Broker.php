<?php

namespace Guandeng\Rabbitmq\Broker;

use Guandeng\Rabbitmq\Exception\BrokerException;
use Guandeng\Rabbitmq\Handlers\Handler;
use Guandeng\Rabbitmq\Message\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class Broker extends AMQPChannel
{
    public $config;
    public $connect;
    public $defaultRoutingKey;
    public $exchange;
    public $exchange_declare;
    public $queue_declare   = [];
    public $consumeTimeout  = 0;
    public static $consumer = 1;
    public static $pulisher = 2;

    public $exchange_attributes = [
        'passive'     => false,
        'durable'     => true, //交换器持久化
        'auto_delete' => false,
        'internal'    => false,
        'nowait'      => false,
    ];
    public $queue_attributes = [
        'passive'     => false,
        'durable'     => true, //队列持久化
        'auto_delete' => true,
        'internal'    => false,
        'nowait'      => false,
        'exclusive'   => false,
    ];

    public function __construct()
    {
        $this->app       = app();
        $this->queues    = $this->app['config']['rabbitmq']['queues'];
        $this->exchanges = $this->app['config']['rabbitmq']['exchanges'];
        $this->config    = $this->app['config']['rabbitmq']['hosts'][0];

        $this->createConnet();

        parent::__construct($this->connect);
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$arguments);
        }
        return false;
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
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['vhost'],
        );
    }
    /**
     * 交换器设置
     *
     * @param [type] $exchange
     * @return object
     */
    public function exchange($exchange)
    {
        $this->setExchangeInfo($exchange);
        $this->setExchange();
        $this->setExchangeAttributes();
        $this->setExchangeBind();
        $this->exchangeDeclare();
        return $this;
    }

    /**
     * 队列设置
     *
     * @param array $queue_info
     */
    public function queue(array $consumer_info)
    {
        $this->setConsumer($consumer_info);
        $this->setQueueInfo();
        $this->setHandlers();
        $this->setPrefetchCount();
        $this->setQueue();
        $this->setQueueAttributes();
        $this->setQueueBind();
        return $this;
    }

    /**
     * 消费者信息
     *
     * @param array $consumer
     * @return void
     */
    public function setConsumer(array $consumer_info)
    {
        $this->consumer_info = $consumer_info;
        return $this;
    }
    /**
     * 队列信息
     *
     * @param [type] $queue
     * @return void
     */
    public function setQueueInfo($queue = null)
    {
        if ($queue) {
            $this->queue_info = $this->queues[$queue];
        } else {
            $this->queue_info = $this->queues[$this->consumer_info['queue']];
        }
        return $this;
    }
    /**
     *
     *
     * @param [type] $queue
     * @return void
     */
    public function getQueueName($queue)
    {
        return $this->queues[$queue]['name'] ?? null;
    }

    /**
     * 交换器名称
     *
     * @param [type] $exchange
     * @return void
     */
    public function getExchangeName($exchange)
    {
        return $this->exchanges[$exchange]['name'] ?? null;
    }

    /**
     * 回调接口
     *
     * @return void
     */
    public function setHandlers()
    {
        $this->handlers = $this->consumer_info['handlers'];
        return $this;
    }

    /**
     * 可接收最大数量
     *
     * @param [intger] $prefetch_count
     * @return void
     */
    public function setPrefetchCount()
    {
        $this->prefetch_count = $this->consumer_info['prefetch_count'];
        return $this;
    }

    /**
     * 设置队列
     *
     * @return void
     */
    public function setQueue()
    {
        $this->queue = $this->queue_info['name'];
        return $this;
    }

    /**
     * 队列属性
     *
     * @return void
     */
    public function setQueueAttributes()
    {
        $this->queue_attributes = array_merge($this->queue_attributes, $this->queue_info['attributes'] ?? []);
        return $this;
    }

    /**
     * 队列绑定交换器
     *
     * @return void
     */
    public function setQueueBind()
    {
        $this->queue_binds = $this->queue_attributes['binds'];
        return $this;
    }

    /**
     * 消息属性配置
     *
     * @return void
     */
    public function setMessageConfig()
    {
        $this->message_config = [
            'delivery_mode' => $this->exchange_attributes['message']['delivery_mode'] ?? null,
            'expiration'    => $this->exchange_attributes['message']['expiration'] ?? null,
        ];
        return $this;
    }

    public function setAmqpTable()
    {
        $this->amqp_table = $this->exchange_attributes['amqp_table'] ?? [];
        return $this;
    }

    /**
     * 交换器名称
     *
     * @return void
     */
    public function setExchange()
    {
        $this->exchange = $this->exchange_info['name'];
        return $this;
    }

    /**
     * 交换器模式
     *
     * @return void
     */
    public function getExchangeType()
    {
        if (!in_array($this->exchange_attributes['exchange_type'],
            [
                AMQPExchangeType::DIRECT,
                AMQPExchangeType::FANOUT,
                AMQPExchangeType::TOPIC,
            ]
        )) {
            return false;
        }
        $this->exchange_type = $this->exchange_attributes['exchange_type'];
        return $this;
    }

    /**
     * 交换器信息
     *
     * @param [type] $exchange
     * @return void
     */
    public function setExchangeInfo($exchange)
    {
        $this->exchange_info = $this->exchanges[$exchange];
        return $this;
    }

    /**
     * 交换器绑定
     *
     * @return void
     */
    public function setExchangeBind()
    {
        $this->exchange_binds = $this->exchange_attributes['binds'];
        return $this;
    }

    /**
     * 交换器声明
     *
     * @return void
     */
    public function exchangeDeclare()
    {
        $attributes = $this->exchange_attributes;
        $this->exchange_declare(
            $this->exchange,
            $attributes['exchange_type'],
            $attributes['passive'],
            $attributes['durable'],
            $attributes['auto_delete'],
            $attributes['internal'],
            $attributes['nowait'],
        );
        return $this;
    }

    /**
     * 交换器属性
     *
     * @return void
     */
    public function setExchangeAttributes()
    {
        $this->exchange_attributes = array_merge(
            $this->exchange_attributes,
            $this->exchange_info['attributes'] ?? []
        );
        return $this;
    }

    /**
     * 生产消息
     */
    public function publish($messages)
    {
        foreach ($this->exchange_binds as $bind) {
            $this->queueDeclareBind(static::$pulisher, $bind['queue'], $bind['routing_key'] ?? '', $this->exchange);
            $this->batch_basic_publish(
                (
                    new Message(
                        $messages,
                        $bind['routing_key'] ?? '',
                        $this->message_config ?? []
                    )
                )->getAMQPMessage(),
                $this->exchange,
                $bind['routing_key'] ?? ''
            );
            $this->publish_batch();
        }
    }

    /**
     * 声明队列
     * @param $queue
     * @param $routing_key
     * @param $exchange
     */
    protected function queueDeclareBind(int $bind_type, $queue, $routing_key, $exchange = null)
    {
        $this->setMessageConfig();
        $this->setAmqpTable();
        // 生产者绑定
        if ($bind_type == static::$pulisher) {
            $this->setQueueInfo($queue);
            $this->setQueue();
            $this->setQueueAttributes();
            $queue = $this->getQueueName($queue);
        }
        $this->queue_declare(
            $queue,
            $this->queue_attributes['passive'],
            $this->queue_attributes['durable'],
            $this->queue_attributes['exclusive'],
            $this->queue_attributes['auto_delete'],
            $this->queue_attributes['nowait'],
            $this->amqp_table ?? [],
        );
        // dd($queue,$this->queue_attributes,$this->amqp_table);
        if ($exchange) {
            $this->queue_bind(
                $queue,
                $exchange,
                $routing_key ?? null,
                false,
                []
            );
        }
        return $this;
    }

    protected function setNoWait($nowait = false)
    {
        $this->nowait = false;
        return $this;
    }
    /**
     * 延迟绑定设置
     */
    public function setDeadLettle($delayExName, $ttl, $queueName)
    {
        $this->tale = new AMQPTable([
            'x-dead-letter-exchange'    => $delayExName,
            'x-message-ttl'             => $ttl, //消息存活时间，单位毫秒
            'x-dead-letter-routing-key' => $queueName,
        ]);
        return $this;
    }

    /**
     * Starts to listen to a queue for incoming messages.
     * @param array $handlers Array of handler class instances
     * @param null $routingKey
     * @param array $options
     * @return bool
     * @internal param string $queueName The AMQP queue
     */
    public function consume()
    {
        $handlersMap = [];
        foreach ($this->handlers as $handlerClassPath) {
            if (!class_exists($handlerClassPath)) {
                $handlerClassPath = "Guandeng\\Rabbitmq\\Handlers\\DefaultHandler";
                if (!class_exists($handlerClassPath)) {
                    throw new BrokerException(
                        "Class $handlerClassPath was not found!"
                    );
                }
            }
            $handlerOb                                                = new $handlerClassPath();
            $classPathParts                                           = explode("\\", $handlerClassPath);
            $handlersMap[$classPathParts[count($classPathParts) - 1]] = $handlerOb;
        }
        foreach ($this->queue_binds as $bind) {
            $this->queueDeclareBind(static::$consumer, $this->queue, $bind['routing_key'] ?? '', $this->getExchangeName($bind['exchange']));
            // prefetch_count 1表示发送一条消息
            $this->basic_qos(
                ($this->prefetch_size ?? null),
                ($this->prefetch_count ?? 1),
                ($this->a_global ?? null)
            );
            $this->basic_consume(
                $this->queue,
                ($this->consumer_tag ?? ''),
                ($this->no_local ?? false),
                ($this->no_ack ?? false),
                ($this->exclusive ?? false),
                ($this->no_wait ?? false),
                function (AMQPMessage $amqpMsg) use ($handlersMap) {
                    $msg = Message::fromAMQPMessage($amqpMsg);
                    $this->handleMessage($msg, $handlersMap);
                }
            );
            return $this->waitConsume();
        }
    }

    /**
     * @param Message $msg
     * @param array   $handlersMap
     * @return bool
     */
    public function handleMessage(Message $msg, $handlersMap)
    {
        if (is_string($handlersMap)) {
            $handlersMap = [$handlersMap];
        }
        /* Try to process the message */
        foreach ($handlersMap as $handler) {
            $retVal = $handler->process($msg);
            switch ($retVal) {
                case Handler::RV_SUCCEED_STOP:
                    /* Handler succeeded, you MUST stop processing */
                    return $handler->handleSucceedStop($msg);

                case Handler::RV_SUCCEED_CONTINUE:
                    /* Handler succeeded, you SHOULD continue processing */
                    $handler->handleSucceedContinue($msg);
                    break;
                case Handler::RV_PASS:
                    /**
                     * Just continue processing (I have no idea what
                     * happened in the handler)
                     */
                    break;

                case Handler::RV_FAILED_STOP:
                    /* Handler failed and MUST stop processing */
                    return $handler->handleFailedStop($msg);

                case Handler::RV_FAILED_REQUEUE:
                    /**
                     * Handler failed and MUST stop processing but the message
                     * will be rescheduled
                     */
                    return $handler->handleFailedRequeue($msg);

                case Handler::RV_FAILED_REQUEUE_STOP:
                    /**
                     * Handler failed and MUST stop processing but the message
                     * will be rescheduled
                     */
                    return $handler->handleFailedRequeueStop($msg, true);

                case Handler::RV_FAILED_CONTINUE:
                    /* Well, handler failed, but you may try another */
                    $handler->handleFailedContinue($msg);
                    break;

                default:
                    return false;
            }

        }
        /* If haven't return yet, send an ACK */
        $msg->sendAck();
    }

    /**
     * @param array $options
     * @return bool
     */
    protected function waitConsume($options = [])
    {
        $consume = true;
        while (count($this->callbacks) && $consume) {
            try {
                $options["non_blocking"] = true;
                $allowed_methods         = $options["allowed_methods"] ?? null;
                $non_blocking            = $options["non_blocking"] ?? false;
                $this->wait($allowed_methods, $non_blocking, $this->consumeTimeout);
            } catch (AMQPTimeoutException $e) {
                if ($e->getMessage() === "The connection timed out after {$this->consumeTimeout} sec while awaiting incoming data") {
                    $consume = false;
                } else {
                    throw ($e);
                }
            }
        }
        return true;
    }
}
