<?php

namespace ProductBundle\Application\UseCases\UpdateProduct;

use ProductBundle\Domain\Repository\ProductRepository;
use ProductBundle\Domain\ValueObject\ProductName;
use ProductBundle\Domain\ValueObject\ProductPrice;
use ProductBundle\Domain\ValueObject\ProductStock;
use ProductBundle\Infrastructure\Event\DomainEventDispatcher;

class UpdateProductHandler
{
    public function __construct(
        private ProductRepository     $repository,
        private DomainEventDispatcher $eventDispatcher,
        private UpdateProductValidator $validator,
    )
    {
    }

    public function __invoke(UpdateProductCommand $command): void
    {
        $this->validator->validate($command);
        $product = $this->repository->find($command->id);

        $product->setName(new ProductName($command->name));
        $product->setPrice(new ProductPrice($command->price));
        $product->setStock(new ProductStock($command->stock));

        $this->repository->update($product);
        $this->eventDispatcher->dispatchAll($product->pullDomainEvents());
    }
}
