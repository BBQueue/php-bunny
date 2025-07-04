<?php

declare(strict_types=1);

namespace BBQueue\Tests\Bunny;

use BBQueue\Bunny\Context;
use Bunny\ChannelInterface;
use Bunny\ClientInterface;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

final class ContextTest extends AsyncTestCase
{
    #[Test]
    public function createMessage(): void
    {
        $client = Mockery::mock(ClientInterface::class);

        $message = (new Context($client, 123))->createMessage();

        self::assertSame('', $message->getBody());
        self::assertSame([], $message->getProperties());
        self::assertSame([], $message->getHeaders());
    }

    #[Test]
    public function closeIsConnected(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->expects('isConnected')->andReturn(true)->once();
        $client->expects('disconnect')->once();

        (new Context($client, 123))->close();
    }

    #[Test]
    public function closeIsntConnected(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->expects('isConnected')->andReturn(false)->once();
        $client->expects('disconnect')->never();

        (new Context($client, 123))->close();
    }

    #[Test]
    public function openChannel(): void
    {
        $channel = Mockery::mock(ChannelInterface::class);
        $channel->expects('qos')->with(0, 123)->once();

        $client = Mockery::mock(ClientInterface::class);
        $client->expects('channel')->andReturn($channel)->once();

        (new Context($client, 123))->createProducer();
    }
}
