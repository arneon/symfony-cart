<?php

namespace CartBundle\Infrastructure\EventListener;

use CartBundle\Domain\Repository\CartRepository;
use CartBundle\Domain\ValueObject\ProductId;
use CartBundle\Domain\ValueObject\ProductName;
use CartBundle\Domain\ValueObject\ProductPrice;
use ProductBundle\Domain\Event\ProductUpdatedEvent;
use ProductBundle\Domain\Repository\ProductRepository;

class ProductUpdatedEventListener
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly ProductRepository $productRepository
    ) {}

    public function __invoke(ProductUpdatedEvent $event): void
    {
        $product = $this->productRepository->find($event->productId);

        $carts = $this->cartRepository->findOpenCartsWithProduct(new ProductId($event->productId));

        foreach ($carts as $cart) {
            foreach ($cart->getItems() as $item) {
                if ($item->getProductId()->value() === $event->productId) {
                    $item->updateNameAndPrice(
                        new ProductName($product->getName()->value()),
                        new ProductPrice($product->getPrice()->value())
                    );
                }
            }
            $this->cartRepository->save($cart);
        }
    }
}
