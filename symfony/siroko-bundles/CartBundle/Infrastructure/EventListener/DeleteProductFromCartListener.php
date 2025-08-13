<?php

namespace CartBundle\Infrastructure\EventListener;

use CartBundle\Domain\Event\DeleteProductFromCartEvent as Event;
use Elasticsearch\Client;
use Psr\Log\LoggerInterface;

final class DeleteProductFromCartListener
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
                'id'      => sprintf('%s-%s-%d', $data['cart_code'], $data['product_id'], $event->getOccurredAt()->getTimestamp()),
                'body'    => [
                    'event'       => Event::eventName(),
                    'cart_code'   => $data['cart_code'],
                    'product_id'  => $data['product_id'],
                    'occurred_at' => $event->getOccurredAt()->format(DATE_ATOM),
                ],
                'refresh' => 'wait_for',
            ]);

            $this->logger?->info('ES indexed ProductDeletedFromCart', [
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
