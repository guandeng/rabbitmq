<?php

namespace Guandeng\Rabbitmq\Facades;

use Illuminate\Support\Facades\Facade;

class RabbitMQ extends Facade{

	protected static function getFacadeAccessor()
	{
		return 'Guandeng\Rabbitmq\RabbitMQ';
	}
} 