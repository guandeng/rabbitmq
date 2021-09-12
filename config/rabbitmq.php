<?php

return [
    'host'          => env('RABBITMQ_HOST', '127.0.0.1'),
    'port'          => env('RABBITMQ_HOST', 5672),
    'user'          => env('RABBITMQ_USER', 'root'),
    'password'      => env('RABBITMQ_PASS', '123456'),
    'vhost'         => env('RABBITMQ_VHOST', '/'),
    'default_queue' => env('RABBITMQ_DEFAULT_QUEUE', 'queue'),
    'exchange'      => env('RABBITMQ_EXCHANGE', ''),
];
