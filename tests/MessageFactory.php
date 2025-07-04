<?php

declare(strict_types=1);

namespace BBQueue\Tests\Bunny;

use BBQueue\Bunny\Message;

final class MessageFactory
{
    public static function createMessage(): Message
    {
        $message = new Message();
        $message->setHeader('head', 'hair-tie');
        $message->setProperty('property', 'booty');
        $message->setBody('body');
        $message->setProperty('consumerTag', 'consumerTag');
        $message->setProperty('deliveryTag', 666);
        $message->setProperty('redelivered', true);
        $message->setProperty('exchange', 'exchange');

        return $message;
    }
}
