<?php

declare(strict_types=1);

namespace BBQueue\Bunny;

use Bunny\ChannelInterface;
use Bunny\ClientInterface;
use Interop\Queue\Consumer as ConsumerContract;
use Interop\Queue\Context as ContextContract;
use Interop\Queue\Destination;
use Interop\Queue\Message as MessageContract;
use Interop\Queue\Producer as ProducerContract;
use Interop\Queue\Queue as QueueContract;
use Interop\Queue\SubscriptionConsumer as SubscriptionConsumerContract;
use Interop\Queue\Topic as TopicContract;

use function assert;
use function bin2hex;
use function random_bytes;

final readonly class Context implements ContextContract
{
    private const int PREFETCH_SIZE               = 0;
    private const int RANDOM_QUEUE_NAME_BYTE_SIZE = 13;

    public function __construct(private ClientInterface $client, private int $prefetchCount)
    {
    }

    /**
     * @param array<string, mixed> $properties
     * @param array<string, mixed> $headers
     *
     * @phpstan-ignore method.childParameterType,method.childParameterType
     */
    public function createMessage(string $body = '', array $properties = [], array $headers = []): MessageContract
    {
        $message = new Message();
        $message->setBody($body);
        $message->setProperties($properties);
        $message->setHeaders($headers);

        return $message;
    }

    public function createTopic(string $topicName): TopicContract
    {
        return new Topic($topicName);
    }

    public function createQueue(string $queueName): QueueContract
    {
        return new Queue($queueName);
    }

    public function createTemporaryQueue(): QueueContract
    {
        return new Queue(bin2hex(random_bytes(self::RANDOM_QUEUE_NAME_BYTE_SIZE)));
    }

    public function createProducer(): ProducerContract
    {
        return new Producer($this->openChannel());
    }

    public function createConsumer(Destination $destination): ConsumerContract
    {
        assert($destination instanceof Queue);

        return new Consumer($destination, $this->openChannel());
    }

    public function createSubscriptionConsumer(): SubscriptionConsumerContract
    {
        return new SubscriptionConsumer();
    }

    public function purgeQueue(QueueContract $queue): void
    {
        // TODO: Implement purgeQueue() method.
    }

    public function close(): void
    {
        if (! $this->client->isConnected()) {
            return;
        }

        $this->client->disconnect();
    }

    private function openChannel(): ChannelInterface
    {
        $channel = $this->client->channel();
        $channel->qos(self::PREFETCH_SIZE, $this->prefetchCount);

        return $channel;
    }
}
