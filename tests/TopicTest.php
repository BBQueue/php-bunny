<?php

declare(strict_types=1);

namespace BBQueue\Tests\Bunny;

use BBQueue\Bunny\Topic;
use PHPUnit\Framework\Attributes\Test;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

final class TopicTest extends AsyncTestCase
{
    #[Test]
    public function getTopicName(): void
    {
        $topic = new Topic('topic');
        self::assertSame('topic', $topic->getTopicName());
    }
}
