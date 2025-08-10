<?php

namespace CartBundle\Application\UseCases\UpdateCartProductQty;

use CartBundle\Domain\Repository\CartRepository;
use ProductBundle\Domain\Repository\ProductRepository;
use CartBundle\Domain\Model\Cart;
use CartBundle\Domain\Model\CartItem;
use CartBundle\Domain\ValueObject\CartCode;
use CartBundle\Domain\ValueObject\CustomerId;
use CartBundle\Domain\ValueObject\ProductId;
use CartBundle\Domain\ValueObject\ProductName;
use CartBundle\Domain\ValueObject\ProductPrice;
use CartBundle\Domain\ValueObject\ProductQty;
use CartBundle\Infrastructure\Event\DomainEventDispatcher;
use Ramsey\Uuid\Uuid;

class UpdateCartProductQtyHandler
{
    public function __construct(
        private CartRepository $repository,
        private DomainEventDispatcher $eventDispatcher,
        private UpdateCartProductQtyValidator $validator
    ) {}

    public function __invoke(UpdateCartProductQtyCommand $command): string
    {
        $this->validator->validate($command);

        $cartCode = new CartCode($command->cartCode);
        $cart = $this->repository->findByCode($cartCode);
        $cart->updateItemQuantity($command->productId, $command->qty);

        $this->repository->save($cart);
        $this->eventDispatcher->dispatchAll($cart->pullDomainEvents());

        return $cartCode->value();
    }
}
