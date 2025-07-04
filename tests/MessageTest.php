<?php

declare(strict_types=1);

namespace BBQueue\Tests\Bunny;

use BBQueue\Bunny\Message;
use BBQueue\Bunny\Queue;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

final class MessageTest extends AsyncTestCase
{
    #[Test]
    public function toAndFromBunnyMessage(): void
    {
        $bunnyMessage = Message::toBunnyMessage(new Queue('Q'), MessageFactory::createMessage());
        $message      = Message::fromBunnyMessage($bunnyMessage);

        self::assertSame($message->getBody(), $bunnyMessage->content);
        self::assertSame($message->getProperty('consumerTag'), $bunnyMessage->consumerTag);
        self::assertSame($message->getProperty('deliveryTag'), $bunnyMessage->deliveryTag);
        self::assertSame($message->getProperty('redelivered'), $bunnyMessage->redelivered);
        self::assertSame($message->getProperty('exchange'), $bunnyMessage->exchange);
        self::assertSame($message->getHeaders(), $bunnyMessage->headers);
    }
}
