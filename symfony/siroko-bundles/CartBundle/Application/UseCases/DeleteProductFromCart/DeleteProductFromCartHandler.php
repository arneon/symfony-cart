<?php

namespace CartBundle\Application\UseCases\DeleteProductFromCart;

use CartBundle\Domain\Repository\CartRepository;
use CartBundle\Domain\ValueObject\CartCode;
use CartBundle\Infrastructure\Event\DomainEventDispatcher;

class DeleteProductFromCartHandler
{
    public function __construct(
        private CartRepository $repository,
        private DomainEventDispatcher $eventDispatcher,
        private DeleteProductFromCartValidator $validator
    ) {}

    public function __invoke(DeleteProductFromCartCommand $command): string
    {
        $this->validator->validate($command);

        $cartCode = new CartCode($command->cartCode);
        $cart = $this->repository->findByCode($cartCode);
        $cart->removeItem($command->productId);

        $this->repository->save($cart);
        $this->eventDispatcher->dispatchAll($cart->pullDomainEvents());

        return $cartCode->value();
    }
}
