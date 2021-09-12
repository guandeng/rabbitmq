<?php

namespace Guandeng\Rabbitmq\Handlers;

use Guandeng\Rabbitmq\Message\Message;

/**
 * Class DefaultHandler
 * @package Kontoulis\RabbitMQLaravel\Handlers
 */
class DefaultHandler extends Handler
{

    /**
     * Tries to process the incoming message.
     * @param Message $msg
     * @return int One of the possible return values defined as Handler
     * constants.
     */

    public function process(Message $msg)
    {
        return $this->handleSuccess($msg);

    }

    /**
     * @param $msg
     * @return int
     */
    protected function handleSuccess($msg)
    {
        var_dump($msg);
        /**
         * For more Handler return values see the parent class
         */
        return Handler::RV_SUCCEED_STOP;
    }
}