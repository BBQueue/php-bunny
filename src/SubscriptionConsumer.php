<?php

declare(strict_types=1);

namespace BBQueue\Bunny;

use Interop\Queue\Consumer;
use Interop\Queue\SubscriptionConsumer as SubscriptionConsumerContract;

final readonly class SubscriptionConsumer implements SubscriptionConsumerContract
{
    public function consume(int $timeout = 0): void
    {
        // TODO: Implement consume() method.
    }

    public function subscribe(Consumer $consumer, callable $callback): void
    {
        // TODO: Implement subscribe() method.
    }

    public function unsubscribe(Consumer $consumer): void
    {
        // TODO: Implement unsubscribe() method.
    }

    public function unsubscribeAll(): void
    {
        // TODO: Implement unsubscribeAll() method.
    }
}
