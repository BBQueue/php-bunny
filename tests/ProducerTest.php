<?php

declare(strict_types=1);

namespace BBQueue\Tests\Bunny;

use BBQueue\Bunny\Message;
use BBQueue\Bunny\Producer;
use BBQueue\Bunny\Queue;
use Bunny\ChannelInterface;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

final class ProducerTest extends AsyncTestCase
{
    #[Test]
    public function send(): void
    {
        $queue   = new Queue('Q');
        $message = new Message();
        $message->setBody('abc');
        $message->setHeader('name', 'value');
        $channel = Mockery::mock(ChannelInterface::class);
        $channel->shouldReceive('publish')->with($message->getBody(), $message->getHeaders(), '', $queue->getQueueName())->once();

        $producer = new Producer($channel);
        $producer->send($queue, $message);
    }
}
