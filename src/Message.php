<?php

declare(strict_types=1);

namespace BBQueue\Bunny;

use Interop\Queue\Impl\MessageTrait;
use Interop\Queue\Message as MessageContract;

final readonly class Message implements MessageContract
{
    use MessageTrait;

    public static function fromBunnyMessage(\Bunny\Message $bunnyMessage): MessageContract
    {
        $message = new Message();
        $message->setBody($bunnyMessage->content);
        $message->setProperty('consumerTag', $bunnyMessage->consumerTag);
        $message->setProperty('deliveryTag', $bunnyMessage->deliveryTag);
        $message->setProperty('redelivered', $bunnyMessage->redelivered);
        $message->setProperty('exchange', $bunnyMessage->exchange);
        $message->setHeaders($bunnyMessage->headers);

        return $message;
    }
}
