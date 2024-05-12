<?php

declare(strict_types=1);

namespace BBQueue\Bunny;

use Bunny\ChannelInterface;
use Interop\Queue\Consumer as ConsumerContract;
use Interop\Queue\Destination;
use Interop\Queue\Message as MessageContract;
use Interop\Queue\Queue as QueueContract;
use React\EventLoop\Loop;
use React\Promise\Deferred;

use function React\Async\await;

final readonly class Consumer implements ConsumerContract
{
    public function __construct(private Destination $destination, private ChannelInterface $channel)
    {
    }

    public function getQueue(): QueueContract
    {
        return $this->destination;
    }

    public function receive(int $timeout = 0): MessageContract|null
    {
        $message = $this->channel->get($this->destination->getQueueName());
        if ($message !== null) {
            return Message::fromBunnyMessage($message);
        }

        $deferred = new Deferred();

        $consumerId = $this->channel->consume(static fn (\Bunny\Message $message) => $deferred->resolve(Message::fromBunnyMessage($message)), $this->destination->getQueueName());
        Loop::addTimer($timeout / 1000, static function () use ($deferred): void {
            $deferred->resolve(null);
        });

        return await($deferred->promise());
    }

    public function receiveNoWait(): MessageContract|null
    {
        return $this->receive();
    }

    public function acknowledge(MessageContract $message): void
    {
        $this->channel->ack(
            new \Bunny\Message(
                consumerTag: $message->getProperty('consumerTag'),
                deliveryTag: $message->getProperty('deliveryTag'),
                redelivered: $message->getProperty('redelivered'),
                exchange: $message->getProperty('exchange'),
                routingKey: $this->destination->getQueueName(),
                headers: $message->getHeaders(),
                content: $message->getBody(),
            ),
        );
    }

    public function reject(MessageContract $message, bool $requeue = false): void
    {
        $this->channel->nack(
            new \Bunny\Message(
                consumerTag: $message->getProperty('consumerTag'),
                deliveryTag: $message->getProperty('deliveryTag'),
                redelivered: $message->getProperty('redelivered'),
                exchange: $message->getProperty('exchange'),
                routingKey: $this->destination->getQueueName(),
                headers: $message->getHeaders(),
                content: $message->getBody(),
            ),
            false,
            $requeue,
        );
    }
}
