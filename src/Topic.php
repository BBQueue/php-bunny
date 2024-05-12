<?php

declare(strict_types=1);

namespace BBQueue\Bunny;

use Interop\Queue\Topic as TopicContract;

final readonly class Topic implements TopicContract
{
    public function __construct(private string $topic)
    {
    }

    public function getTopicName(): string
    {
        return $this->topic;
    }
}
