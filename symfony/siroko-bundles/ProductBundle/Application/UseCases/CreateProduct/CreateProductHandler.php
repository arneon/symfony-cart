<?php

namespace ProductBundle\Application\UseCases\CreateProduct;

use ProductBundle\Domain\Event\ProductCreatedEvent;
use ProductBundle\Domain\Exception\ValidationException;
use ProductBundle\Domain\Repository\ProductRepository;
use ProductBundle\Domain\Model\Product;
use ProductBundle\Domain\ValueObject\ProductName;
use ProductBundle\Domain\ValueObject\ProductPrice;
use ProductBundle\Domain\ValueObject\ProductStock;
use ProductBundle\Infrastructure\Event\DomainEventDispatcher;

class CreateProductHandler
{
    public function __construct(
        private ProductRepository $repository,
        private DomainEventDispatcher $eventDispatcher,
        private CreateProductValidator $validator,
    ) {}

    public function __invoke(CreateProductCommand $command): int
    {
        try {
            $this->validator->validate($command);
        }catch(ValidationException $e) {
            throw new ValidationException($e->getErrors(), $e->getErrorCode());
        }

        try{
            $product = new Product(
                new ProductName($command->name),
                new ProductPrice($command->price),
                new ProductStock($command->stock),
            );

            $id = $this->repository->save($product);
            $product->setId($id);
            $this->eventDispatcher->dispatchAll($product->pullDomainEvents());

            return $id;
        }catch(ValidationException $e) {
            throw new ValidationException($e->getErrors(), $e->getErrorCode());
        }
    }
}
