<?php

declare(strict_types=1);

namespace BBQueue\Bunny;

use Bunny\ChannelInterface;
use Bunny\Message as BunnyMessage;
use Interop\Queue\Consumer as ConsumerContract;
use Interop\Queue\Destination;
use Interop\Queue\Message as MessageContract;
use Interop\Queue\Queue as QueueContract;
use React\EventLoop\Loop;
use React\Promise\Deferred;

use function React\Async\async;
use function React\Async\await;

final class Consumer implements ConsumerContract
{
    private string $consumerTag;

    /**
     * @var \SplQueue<MessageContract>
     */
    private \SplQueue $cacheMessages;

    /**
     * @var \SplQueue<Deferred>
     */
    private \SplQueue $waitingReceives;

    public function __construct(private readonly Destination $destination, private readonly ChannelInterface $channel)
    {
        $this->cacheMessages = new \SplQueue();
        $this->waitingReceives = new \SplQueue();
        $this->consumerTag = $this->channel->consume(
            function (BunnyMessage $message): void {
                $message = Message::fromBunnyMessage($message);
                if ($this->waitingReceives->isEmpty()) {
                    $this->cacheMessages->enqueue($message);
                    return;
                }

                $this->waitingReceives->dequeue()->resolve($message);
            },
            $this->destination->getQueueName(),
        )->consumerTag;
    }

    public function __destruct()
    {
//        $this->channel->cancel($this->consumerTag);
    }

    public function getQueue(): QueueContract
    {
        return $this->destination;
    }

    public function receive(int $timeout = 0): MessageContract|null
    {
        if ($this->cacheMessages->isEmpty()) {
            $deferred = new Deferred();
            $this->waitingReceives->enqueue($deferred);

            return await($deferred->promise());
        }

        return $this->cacheMessages->dequeue();
    }

    public function receiveNoWait(): MessageContract|null
    {
        return $this->receive();
    }

    public function acknowledge(MessageContract $message): void
    {
        $this->channel->ack(
            new BunnyMessage(
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
            new BunnyMessage(
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
