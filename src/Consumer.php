<?php

declare(strict_types=1);

namespace BBQueue\Bunny;

use Bunny\ChannelInterface;
use Bunny\Message as BunnyMessage;
use Interop\Queue\Consumer as ConsumerContract;
use Interop\Queue\Message as MessageContract;
use Interop\Queue\Queue as QueueContract;
use React\Promise\Deferred;
use SplQueue;

use function React\Async\await;

final readonly class Consumer implements ConsumerContract
{
    private const int DEFAULT_TIMEOUT = 0;
    private string $consumerTag;

    /** @var SplQueue<MessageContract> */
    private SplQueue $cacheMessages;

    /** @var SplQueue<Deferred<MessageContract>> */
    private SplQueue $waitingReceives;

    public function __construct(private Queue $destination, private ChannelInterface $channel)
    {
        $this->cacheMessages   = new SplQueue();
        $this->waitingReceives = new SplQueue();
        $this->consumerTag     = $this->channel->consume(
            function (BunnyMessage $bunnyMessage): void {
                $message = Message::fromBunnyMessage($bunnyMessage);
                unset($bunnyMessage);
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
        $this->channel->cancel($this->consumerTag);
    }

    public function getQueue(): QueueContract
    {
        return $this->destination;
    }

    /** @phpstan-ignore shipmonk.uselessNullableReturn,return.unusedType */
    public function receive(int $timeout = self::DEFAULT_TIMEOUT): MessageContract|null
    {
        if ($this->cacheMessages->isEmpty()) {
            /** @var Deferred<MessageContract> $deferred */
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
            Message::toBunnyMessage($this->destination, $message),
        );
    }

    public function reject(MessageContract $message, bool $requeue = false): void
    {
        $this->channel->nack(
            Message::toBunnyMessage($this->destination, $message),
            false,
            $requeue,
        );
    }
}
