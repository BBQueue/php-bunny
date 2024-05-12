<?php

declare(strict_types=1);

namespace BBQueue\Bunny;

use Bunny\Client;
use Interop\Queue\ConnectionFactory as ConnectionFactoryContract;
use Interop\Queue\Context as ContextContract;

final readonly class ConnectionFactory implements ConnectionFactoryContract
{
    public function __construct(private Client $client)
    {
    }

    public function createContext(): ContextContract
    {
        return new Context($this->client);
    }
}
