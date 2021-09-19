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

    ],
    'queues'     => [

    ],
    'publishers' => [

    ],
    'consumers'  => [

    ],
];
