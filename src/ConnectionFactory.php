<?php

declare(strict_types=1);

namespace BBQueue\Bunny;

use Bunny\ClientInterface;
use Interop\Queue\ConnectionFactory as ConnectionFactoryContract;
use Interop\Queue\Context as ContextContract;

final readonly class ConnectionFactory implements ConnectionFactoryContract
{
    private const int DEFAULT_PREFETCH_COUNT = 0;

    /** @phpstan-ignore ergebnis.noConstructorParameterWithDefaultValue,shipmonk.deadMethod */
    public function __construct(private ClientInterface $client, private int $prefetchCount = self::DEFAULT_PREFETCH_COUNT)
    {
    }

    public function createContext(): ContextContract
    {
        return new Context($this->client, $this->prefetchCount);
    }
}
