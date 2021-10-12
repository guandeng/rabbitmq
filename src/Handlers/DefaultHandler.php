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
        $data = $msg->getData(1);
        if (isset($data[0]) && $data[0] == 'reject') {
            dump(__CLASS__ . "收到拒绝消息:" . json_encode($data));
            return Handler::RV_FAILED_STOP; // nack
        }
        dump(__CLASS__ . "收到消息:" . json_encode($data));
        return Handler::RV_SUCCEED_STOP;
    }
}