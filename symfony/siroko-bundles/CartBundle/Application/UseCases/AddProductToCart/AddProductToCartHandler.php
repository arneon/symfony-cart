<?php

namespace CartBundle\Application\UseCases\AddProductToCart;

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

class AddProductToCartHandler
{
    public function __construct(
        private CartRepository $repository,
        private ProductRepository $productRepository,
        private DomainEventDispatcher $eventDispatcher,
        private AddProductToCartValidator $validator
    ) {}

    public function __invoke(AddProductToCartCommand $command): string
    {
        $this->validator->validate($command);

        if($command->cartCode)
        {
            $cartCode = new CartCode($command->cartCode);
            $cart = $this->repository->findByCode($cartCode);
        }
        else
        {
            $cartCode = new CartCode(Uuid::uuid4()->toString());
            $cart = new Cart($cartCode, new CustomerId($command->customerId));
        }

        $product = $this->productRepository->find($command->productId);

        $this->addItemToCart($cart, $product, $command->qty);

        $id = $this->repository->save($cart);
        $cart->setId($id);
        $this->eventDispatcher->dispatchAll($cart->pullDomainEvents());

        return $cartCode->value();
    }

    private function addItemToCart($cart, $product, $qty) : void
    {
        $item = new CartItem(
            new ProductId($product->getId()),
            new ProductName($product->getName()->value()),
            new ProductPrice($product->getPrice()->value()),
            new ProductQty($qty)
        );

        $cart->addItem($item);
    }
}
