<?php

namespace ProductBundle\Infrastructure\EventListener;

use ProductBundle\Domain\Repository\ProductRepository;
use ProductBundle\Domain\ValueObject\ProductStock;
use CartBundle\Domain\Event\StockDecreaseRequested;

class StockDecreaseOnOrderListener
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    )
    {
    }

    public function __invoke(StockDecreaseRequested $event): void
    {
        try {
            $product = $this->productRepository->find($event->getProductId());

            if (!$product) {
                return;
            }

            $newStock = max(0,($product->getStock()->value() - $event->getQuantity()));

            $product->setStock(new ProductStock($newStock));

            $this->productRepository->update($product);

        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
