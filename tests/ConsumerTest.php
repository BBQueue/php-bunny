<?php

declare(strict_types=1);

namespace BBQueue\Tests\Bunny;

use BBQueue\Bunny\Consumer;
use BBQueue\Bunny\Queue;
use Bunny\ChannelInterface;
use Bunny\Message as BunnyMessage;
use Bunny\Protocol\MethodBasicConsumeOkFrame;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

final class ConsumerTest extends AsyncTestCase
{
    #[Test]
    public function cancel(): void
    {
        $frame              = new MethodBasicConsumeOkFrame();
        $frame->consumerTag = 'consumerTag';

        $channel = Mockery::mock(ChannelInterface::class);
        $channel->expects('consume')->with(Mockery::type('callable'), 'Q')->andReturn($frame)->once();
        $channel->expects('cancel')->with($frame->consumerTag)->atLeast()->once();

        (new Consumer(new Queue('Q'), $channel))->__destruct();
    }

    #[Test]
    public function acknowledge(): void
    {
        $frame              = new MethodBasicConsumeOkFrame();
        $frame->consumerTag = 'consumerTag';

        $message = MessageFactory::createMessage();

        $channel = Mockery::mock(ChannelInterface::class);
        $channel->expects('consume')->with(Mockery::type('callable'), 'Q')->andReturn($frame)->once();
        $channel->expects('cancel')->with($frame->consumerTag)->atMost()->once();
        $channel->expects('ack')->with(Mockery::type(BunnyMessage::class))->once();

        (new Consumer(new Queue('Q'), $channel))->acknowledge($message);
    }

    #[Test]
    public function reject(): void
    {
        $frame              = new MethodBasicConsumeOkFrame();
        $frame->consumerTag = 'consumerTag';

        $message = MessageFactory::createMessage();

        $channel = Mockery::mock(ChannelInterface::class);
        $channel->expects('consume')->with(Mockery::type('callable'), 'Q')->andReturn($frame)->once();
        $channel->expects('cancel')->with($frame->consumerTag)->atMost()->once();
        $channel->expects('nack')->with(Mockery::type(BunnyMessage::class), false, false)->once();

        (new Consumer(new Queue('Q'), $channel))->reject($message);
    }
}
