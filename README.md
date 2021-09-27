# Laravel-RabbitMQ
![](https://img.shields.io/badge/stable-1.0.0-brightgreen.svg)
![](https://img.shields.io/badge/autor-guandeng-red.svg)
![](https://img.shields.io/badge/license-MIT-green.svg)

#### 功能列表
- [x] 生产消息
- [x] 消费消息
- [x] 支持队列持久
- [x] 支持消息持久化
- [x] 支持交换机持久化
- [x] 支持生产消息确认机制
- [x] 支持消费消息确认机制
- [x] 支持消息公平调度
- [x] 支持多个工作队列
- [x] 支持死信队列
- [x] 支持延迟队列
- [ ] 支持优先级队列
- [ ] 支持集群配置
- [ ] 停止指定消费 

#### 安装
>  composer require guandeng/rabbitmq-laravel

#### .env配置
```
RABBITMQ_HOST=127.0.0.1
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASS=guest
RABBITMQ_VHOST=/
```
#### rabbitmq.php配置
可参考rabbitmq_example.php

交换机配置
```
exchanges'  => [
        'DirectExchange'     => [
            'name'       => 'my.first.directexchange',
            'attributes' => [
                'exchange_type' => 'direct',
                'binds'         => [
                    [
                        'routing_key' => 'direct_route_key',
                    ],

                ],
                'arguments'     => [
                    'x-dead-letter-exchange'    => 'my.first.dead.directexchange',
                    'x-message-ttl'             => 5000, //消息存活时间，单位毫秒
                    'x-dead-letter-routing-key' => 'dead_direct_route_key',
                ],
                'message'       => [
                    'delivery_mode' => 2,
                    'priority'      => 10,
                ],
            ],
        ]
]
    
```

队列配置
```
'queues'     => [
        'DirectQueue'     => [
            'name'       => 'my.first.directqueue',
            'attributes' => [
                'binds' => [
                    [
                        'exchange'    => 'DirectExchange',
                        'routing_key' => 'direct_route_key',
                    ],
                ],
            ],
        ],
]
```

生产者配置
```
'publishers' => [
    'DirectPublisher'     => 'DirectExchange',
],
```

消费者配置
```
'DirectConsumer'     => [
    'queue'          => 'DirectQueue',
    'prefetch_count' => 1,
    'handlers'       => ["\\App\\QueueHandlers\\MyHandler"],
],
```
#### 生产消息
命令行,指定配置生产消息
```
php artisan publisher:rabbitmq DirectPublisher test
```
伪代码
```
public function publish()
{
    // 向所有生产者发送消息
    $publishers = config('rabbitmq.publishers');
    foreach ($publishers as $publisher => $exchange) {
        $messages = [
            'key' => 'hello world' 
        ];
        app('rabbitmq')->exchange($exchange)->publish($messages);
    }
}
```

#### 消费消息

指定rabbitmq.php消费者配置
```
 php artisan consumer:rabbitmq DirectConsumer
```
处理消息在消费者配置handlers指定回调类中