#### 安装
1、composer
>  composer require guandeng/rabbitmq-laravel
2、添加Service Provider
> Guandeng\Rabbitmq\RabbitMQLaravelServiceProvider ::class
3、添加门面
> 'RabbitMQ' => Guandeng\Rabbitmq\Facades\RabbitMQ::class
4、添加配置
> php artisan vendor:publish