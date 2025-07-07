<?php

declare(strict_types=1);

namespace BBQueue\Bunny;

use Bunny\Message as BunnyMessage;
use Interop\Queue\Destination;
use Interop\Queue\Impl\MessageTrait;
use Interop\Queue\Message as MessageContract;

use function method_exists;

final class Message implements MessageContract
{
    use MessageTrait;

    public static function fromBunnyMessage(BunnyMessage $bunnyMessage): MessageContract
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

    public static function toBunnyMessage(Destination $destination, MessageContract $message): BunnyMessage
    {
        return new BunnyMessage(
            consumerTag: $message->getProperty('consumerTag'),
            deliveryTag: $message->getProperty('deliveryTag'),
            redelivered: $message->getProperty('redelivered'),
            exchange: $message->getProperty('exchange'),
            routingKey: method_exists($destination, 'getQueueName') ? $destination->getQueueName() : '',
            headers: $message->getHeaders(),
            content: $message->getBody(),
        );
    }
}
