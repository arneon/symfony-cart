<?php

namespace ProductBundle\Infrastructure\Query\Redis;

use Predis\Client;
use ProductBundle\Domain\Event\ProductCreatedEvent;
use ProductBundle\Domain\Event\ProductUpdatedEvent;
use ProductBundle\Domain\Event\ProductDeletedEvent;
use ProductBundle\Infrastructure\Persistence\MySQL\DoctrineProductRepository;

class RedisProductSync
{
    public function __construct(
        private Client $redis,
        private DoctrineProductRepository $repository
    ) {}

    public function onProductCreated(ProductCreatedEvent $event): void
    {
        $product = $this->repository->find($event->productId);
        $this->saveToRedis($product);
    }

    public function onProductUpdated(ProductUpdatedEvent $event): void
    {
        $product = $this->repository->find($event->productId);
        $this->saveToRedis($product);
    }

    public function onProductDeleted(ProductDeletedEvent $event): void
    {
        $this->redis->del("product:{$event->productId}");
    }

    private function saveToRedis($product): void
    {
        $id = $product ? $product->getId() : null;

        if ($id !== null) {
            $this->redis->set("product:{$id}", json_encode([
                'id' => $product->getId(),
                'name' => $product->getName()->value(),
                'price' => $product->getPrice()->value(),
                'stock' => $product->getStock()->value(),
            ]));
        }
    }
}
