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
        'DirectExchange' => [
            'name'       => 'my.first.directexchange',
            'attributes' => [
                'exchange_type' => 'direct',
                'passive'       => false,
                'durable'       => true,
                'auto_delete'   => false,
                'internal'      => false,
                'nowait'        => false,
                'binds'         => [
                    [
                        'queue'     => 'my.first.directqueue',
                        'route_key' => '*',
                    ],
                ],
            ],
        ],
        'FanoutExchange' => [
            'name'       => 'my.first.fanoutexchange',
            'attributes' => [
                'exchange_type' => 'fanout',
                'passive'       => false,
                'durable'       => true,
                'auto_delete'   => false,
                'internal'      => false,
                'nowait'        => false,
                'binds'         => [
                    [
                        'queue' => 'my.first.fanoutqueue',
                    ],
                    [
                        'queue' => 'my.first.fanoutqueue2',
                    ],
                ],
            ],
        ],
        'TopicExchange' => [
            'name'       => 'my.first.topicexchange',
            'attributes' => [
                'exchange_type' => 'fanout',
                'passive'       => false,
                'durable'       => true,
                'auto_delete'   => false,
                'internal'      => false,
                'nowait'        => false,
                'binds'         => [
                    [
                        'queue' => 'my.first.topicqueue',
                    ],
                    [
                        'queue' => 'my.first.topicqueue2',
                    ],
                ],
            ],
        ],
    ],
    'queues'     => [
        'DirectQueue' => [
            'name'       => 'my.first.directqueue',
            'attributes' => [
                'passive'     => false,
                'durable'     => true,
                'auto_delete' => false,
                'internal'    => false,
                'nowait'      => false,
                'exclusive'   => false,
                'binds'       => [
                    [
                        'exchange'  => 'my.first.directexchange',
                        'route_key' => '*',
                    ],
                ],
            ],
        ],
        'FanoutQueue' => [
            'name'       => 'my.first.fanoutqueue',
            'attributes' => [
                'passive'     => false,
                'durable'     => true,
                'auto_delete' => false,
                'internal'    => false,
                'nowait'      => false,
                'exclusive'   => false,
                'binds'       => [
                    [
                        'exchange' => 'my.first.fanoutexchange',
                    ],
                ],
            ],
        ],
        'TopicQueue'  => [
            'name'       => 'my.first.topicqueue',
            'attributes' => [
                'passive'     => false,
                'durable'     => true,
                'auto_delete' => false,
                'internal'    => false,
                'nowait'      => false,
                'exclusive'   => false,
                'binds'       => [
                    [
                        'exchange'  => 'my.first.topicexchange',
                        'route_key' => '*',
                    ],
                ],
            ],
        ],
    ],
    'publishers' => [
        'DirectPublisher' => 'DirectExchange',
        'FanoutPublisher' => 'FanoutExchange',
        'TopicPublisher'  => 'TopicExchange',
    ],
    'consumers'  => [
        'DirectConsumer'  => [
            'queue'          => 'DirectQueue',
            'prefetch_count' => 10,
            'handlers'       => ["\\App\\QueueHandlers\\MyHandler"],
        ],
        'FanoutConsumer' => [
            'queue'          => 'FanoutQueue',
            'prefetch_count' => 10,
            'handlers'       => ["\\App\\QueueHandlers\\MyHandler"],
        ],
        'TopicConsumer'  => [
            'queue'          => 'TopicQueue',
            'prefetch_count' => 10,
            'handlers'       => ["\\App\\QueueHandlers\\MyHandler"],
        ],
    ],
];
