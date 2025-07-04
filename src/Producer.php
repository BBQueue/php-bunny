<?php

declare(strict_types=1);

namespace BBQueue\Bunny;

use Bunny\ChannelInterface;
use Interop\Queue\Destination;
use Interop\Queue\Message as MessageContract;
use Interop\Queue\Producer as ProducerContract;

final class Producer implements ProducerContract
{
    private int|null $deliveryDelay = null;
    private int|null $priority      = null;
    private int|null $timeToLive    = null;

    public function __construct(private readonly ChannelInterface $channel)
    {
    }

    public function send(Destination $destination, MessageContract $message): void
    {
        $this->channel->publish(
            $message->getBody(),
            $message->getHeaders(), /** @phpstan-ignore argument.type */
            '',
            $destination instanceof Queue ? $destination->getQueueName() : '',
        );
    }

    public function setDeliveryDelay(int|null $deliveryDelay = null): ProducerContract
    {
        $this->deliveryDelay = $deliveryDelay;

        return $this;
    }

    public function getDeliveryDelay(): int|null
    {
        return $this->deliveryDelay;
    }

    public function setPriority(int|null $priority = null): ProducerContract
    {
        $this->priority = $priority;

        return $this;
    }

    public function getPriority(): int|null
    {
        return $this->priority;
    }

    public function setTimeToLive(int|null $timeToLive = null): ProducerContract
    {
        $this->timeToLive = $timeToLive;

        return $this;
    }

    public function getTimeToLive(): int|null
    {
        return $this->timeToLive;
    }
}
