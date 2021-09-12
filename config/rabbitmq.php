<?php

return [
    'amqp_host'          => env('RABBITMQ_HOST', '127.0.0.1'),
    'amqp_port'          => 5672,
    'amqp_user'          => env('RABBITMQ_USER', 'guest'),
    'amqp_pass'          => env('RABBITMQ_PASS', 'guest'),
    'amqp_vhost'         => env('RABBITMQ_VHOST', '/'),
    'amqp_default_queue' => env('RABBITMQ_DEFAULT_QUEUE', 'queue'),
    'amqp_exchange'      => env('RABBITMQ_EXCHANGE', ''),
];
