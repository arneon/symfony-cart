<?php

namespace UserBundle\Infrastructure\EventListener;

use UserBundle\Domain\Event\RegisteredUserEvent as Event;
use Elasticsearch\Client;
use Psr\Log\LoggerInterface;

final class RegisteredUserListener
{
    public function __construct(
        private Client $client,
        private string $indexName,
        private ?LoggerInterface $logger = null,
    ) {}

    public function __invoke(Event $event): void
    {
        $data = $event->toArray();
        try{
            $response = $this->client->index([
                'index'   => $this->indexName,
                'id'      => sprintf('%s-%d', $data['user_id'], $event->getOccurredAt()->getTimestamp()),
                'body'    => [
                    'event'       => Event::eventName(),
                    'user_id'  => $data['user_id'],
                    'occurred_at' => $event->getOccurredAt()->format(DATE_ATOM),
                ],
                'refresh' => 'wait_for',
            ]);

            $this->logger?->info('ES indexed UserRegistered', [
                'index' => $this->indexName,
                'id'    => $response['_id'] ?? null,
                'result'=> $response['result'] ?? null,
                'data'  => $data,
            ]);
        }catch(\Throwable $e)
        {
            $this->logger?->error('ES index error', [
                'index' => $this->indexName,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
