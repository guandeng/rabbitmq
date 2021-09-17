<?php

namespace Guandeng\Rabbitmq\Broker;

use Guandeng\Rabbitmq\Exception\BrokerException;
use Guandeng\Rabbitmq\Handlers\Handler;
use Guandeng\Rabbitmq\Message\Message;
use Illuminate\Support\Arr;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class Broker extends AMQPChannel
{
    public $config;
    public $connect;
    public $defaultRoutingKey;
    public $exchange;
    public $exchange_declare;
    public $queue_declare  = [];
    public $consumeTimeout = 0;

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
     * 设置路由键
     */
    public function setRouteKey($route_key)
    {
        $this->defaultRoutingKey = $route_key;
        return $this;
    }

    public function exchange($exchange, $type = "direct")
    {
        $this->exchange = $exchange;
        $this->exchange_declare($exchange, $type, false, true, false);
        return $this;
    }

    /**
     * 生产消息（支持多条消息）
     */
    public function publish($messages, $routingKey = null)
    {
        $this->queueDeclareBind($routingKey);
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
            $this->queue_declare['passive'] ?? false,
            $this->queue_declare['durable'] ?? true,
        );

        if ($this->exchange != "") {
            // Bind the queue to the exchange
            $this->queue_bind($routingKey, $this->exchange, $routingKey, $this->nowait ?? false, $this->tale ?? []);
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
     * @param null $routingKey
     * @return mixed
     */
    public function getQueueInfo($routingKey = null)
    {
        if (is_null($routingKey)) {
            // Set the routing key if missing
            $routingKey = $this->defaultRoutingKey;
        }

        $ch    = curl_init();
        $vhost = ($this->config['vhost'] != "/" ? $this->vhost : "%2F");
        $url   = "http://" . $this->config['host'] . ":15672" . "/api/queues/$vhost/" . $routingKey;

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_USERPWD, $this->config['user'] . ":" . $this->config['password']);

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    /**
     * @param $message
     * @param null $routingKey
     */
    public function publishMessage($message, $routingKey = null)
    {
        $this->queueDeclareBind($routingKey);
        $msg = new Message($message, $routingKey, $this->message_options ?? []);
        // Create the message
        $amqpMessage = $msg->getAMQPMessage();
        $this->basic_publish($amqpMessage, $this->exchange, $routingKey);
    }
    /**
     * 消息属性
     */
    public function setMessageOptions($options)
    {
        $this->message_options = $options;
        return $this;
    }

    /**
     * @param callable $callback
     * @param null $routingKey
     * @param array $options
     * @return bool
     */
    public function basicConsume($routingKey = null, $options = [])
    {
        $this->queueDeclareBind($routingKey);

        $this->basic_consume(
            $routingKey,
            '',
            false,
            false,
            false,
            false,
        );

        return $this->waitConsume($options);
    }

    /**
     * Starts to listen to a queue for incoming messages.
     * @param array $handlers Array of handler class instances
     * @param null $routingKey
     * @param array $options
     * @return bool
     * @internal param string $queueName The AMQP queue
     */
    public function listenToQueue(array $handlers, $routingKey = null, $options = [])
    {
        /* Look for handlers */
        $handlersMap = array();
        foreach ($handlers as $handlerClassPath) {
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
        $this->queueDeclareBind($routingKey);
        /* Start consuming */
        // prefetch_count 1表示发送一条消息
        $this->basic_qos(
            (isset($options["prefetch_size"]) ? $options["prefetch_size"] : null),
            (isset($options["prefetch_count"]) ? $options["prefetch_count"] : 1),
            (isset($options["a_global"]) ? $options["a_global"] : null)
        );
        $this->basic_consume(
            $routingKey,
            (isset($options["consumer_tag"]) ? $options["consumer_tag"] : ''),
            (isset($options["no_local"]) ? (bool) $options["no_local"] : false),
            (isset($options["no_ack"]) ? (bool) $options["no_ack"] : false),
            (isset($options["exclusive"]) ? (bool) $options["exclusive"] : false),
            (isset($options["no_wait"]) ? (bool) $options["no_wait"] : false),
            function (AMQPMessage $amqpMsg) use ($handlersMap) {
                $msg = Message::fromAMQPMessage($amqpMsg);
                $this->handleMessage($msg, $handlersMap);
            }
        );
        return $this->waitConsume($options);
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
    /**
     * Recursively filter only null values.
     *
     * @param array $array
     * @return array
     */
    private function filter(array $array): array
    {
        foreach ($array as $index => &$value) {
            if (is_array($value)) {
                $value = $this->filter($value);

                continue;
            }

            // If the value is null then remove it.
            if ($value === null) {
                unset($array[$index]);

                continue;
            }
        }

        return $array;
    }
}
