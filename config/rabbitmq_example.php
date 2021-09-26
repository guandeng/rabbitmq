<?php

return [
    'hosts'      => [
        [
            'host'     => env('RABBITMQ_HOST', '127.0.0.1'),
            'port'     => env('RABBITMQ_HOST', 5672),
            'user'     => env('RABBITMQ_USER', 'guest'),
            'password' => env('RABBITMQ_PASS', 'guest'),
            'vhost'    => env('RABBITMQ_VHOST', '/'),
        ],
    ],
    'exchanges'  => [
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
        ],
        'DirectExchangeDead' => [
            'name'       => 'my.first.dead.directexchange',
            'attributes' => [
                'exchange_type' => 'direct',
                'binds'         => [
                    [
                        'routing_key' => 'dead_direct_route_key',
                    ],

                ],
                'message'       => [
                    'delivery_mode' => 2,

                ],
            ],
        ],
        'FanoutExchange'     => [
            'name'       => 'my.first.fanoutexchange',
            'attributes' => [
                'exchange_type' => 'fanout',
                'binds'         => [
                    [
                        'queue' => 'FanoutQueue',
                    ],
                    [
                        'queue' => 'FanoutQueue',
                    ],
                ],
            ],
        ],
        'TopicExchange'      => [
            'name'       => 'my.first.topicexchange',
            'attributes' => [
                'exchange_type' => 'topic',
                'binds'         => [
                    [
                        'queue'       => 'TopicQueue',
                        'routing_key' => '*',
                    ],
                    [
                        'queue'       => 'TopicQueue',
                        'routing_key' => '*',
                    ],
                ],
            ],
        ],
    ],
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
        'DirectQueueDead' => [
            'name'       => 'my.first.dead.directqueue',
            'attributes' => [
                'binds' => [
                    [
                        'exchange'    => 'DirectExchangeDead',
                        'routing_key' => 'dead_direct_route_key',
                    ],
                ],
            ],
        ],
        'FanoutQueue'     => [
            'name'       => 'my.first.fanoutqueue',
            'attributes' => [
                'binds' => [
                    [
                        'exchange' => 'FanoutExchange',
                    ],
                ],
            ],
        ],
        'TopicQueue'      => [
            'name'       => 'my.first.topicqueue',
            'attributes' => [
                'binds' => [
                    [
                        'exchange'    => 'TopicExchange',
                        'routing_key' => '*',
                    ],
                ],
            ],
        ],
    ],
    'publishers' => [
        'DirectPublisher'     => 'DirectExchange',
        'DirectPublisherDead' => 'DirectExchangeDead',
        // 'FanoutPublisher' => 'FanoutExchange',
        // 'TopicPublisher'  => 'TopicExchange',
    ],
    'consumers'  => [
        'DirectConsumer'     => [
            'queue'          => 'DirectQueue',
            'prefetch_count' => 1,
            'handlers'       => ["\\App\\QueueHandlers\\MyHandler"],
        ],
        'DirectConsumerDead' => [
            'queue'          => 'DirectQueueDead',
            'prefetch_count' => 1,
            'handlers'       => ["\\App\\QueueHandlers\\MyHandler2"],
        ],
        // 'FanoutConsumer' => [
        //     'queue'          => 'FanoutQueue',
        //     'prefetch_count' => 10,
        //     'handlers'       => ["\\App\\QueueHandlers\\MyHandler"],
        // ],
        // 'TopicConsumer'  => [
        //     'queue'          => 'TopicQueue',
        //     'prefetch_count' => 10,
        //     'handlers'       => ["\\App\\QueueHandlers\\MyHandler"],
        // ],
    ],
];
