<?php

declare(strict_types=1);

namespace BBQueue\Bunny;

use Interop\Queue\Queue as QueueContract;

final readonly class Queue implements QueueContract
{
    public function __construct(private string $queue)
    {
    }

    public function getQueueName(): string
    {
        return $this->queue;
    }
}
