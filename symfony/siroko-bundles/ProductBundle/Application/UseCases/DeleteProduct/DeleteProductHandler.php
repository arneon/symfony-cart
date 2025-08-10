<?php

namespace ProductBundle\Application\UseCases\DeleteProduct;

use ProductBundle\Domain\Event\ProductDeletedEvent;
use ProductBundle\Domain\Repository\ProductRepository;
use ProductBundle\Infrastructure\Event\DomainEventDispatcher;

class DeleteProductHandler
{
    public function __construct(
        private ProductRepository     $repository,
        private DomainEventDispatcher $eventDispatcher,
        private DeleteProductValidator $validator,
    )
    {
    }

    public function __invoke(DeleteProductCommand $command): void
    {
        $this->validator->validate($command);
        $product = $this->repository->find($command->id);

        $this->repository->delete($product);
        $product->recordEvent(new ProductDeletedEvent($command->id));
        $this->eventDispatcher->dispatchAll($product->pullDomainEvents());
    }
}
