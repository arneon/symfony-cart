<?php

namespace CartBundle\Infrastructure\Persistence\Mysql\Mapper;

use CartBundle\Domain\Model\Cart;
use CartBundle\Domain\Model\CartItem;
use CartBundle\Domain\ValueObject\CartCode;
use CartBundle\Domain\ValueObject\CartId;
use CartBundle\Domain\ValueObject\CustomerId;
use CartBundle\Domain\ValueObject\ProductId;
use CartBundle\Domain\ValueObject\ProductName;
use CartBundle\Domain\ValueObject\ProductPrice;
use CartBundle\Domain\ValueObject\ProductQty;
use CartBundle\Infrastructure\Persistence\Mysql\CartDoctrineEntity;
use CartBundle\Infrastructure\Persistence\Mysql\CartItemDoctrineEntity;
use Doctrine\Common\Collections\ArrayCollection;

class CartMapper
{
    public function toDomain(CartDoctrineEntity $entity): Cart
    {
        $cart = new Cart(
            new CartCode($entity->getCartCode()),
            new CustomerId($entity->getCustomerId())
        );

        $cartId = $entity->getId();
        if($cartId)
        {
            $cart->setId($cartId);

        }

        $reflection = new \ReflectionClass($cart);
        foreach (['createdAt', 'updatedAt', 'checkedOutAt', 'status'] as $prop) {
            $property = $reflection->getProperty($prop);
            $property->setAccessible(true);
            $property->setValue($cart, $entity->{'get' . ucfirst($prop)}());
        }

        foreach ($entity->getItems() as $itemEntity) {
            $cart->addItem(
                new CartItem(
                    new ProductId($itemEntity->getProductId()),
                    new ProductName($itemEntity->getProductName()),
                    new ProductPrice((float)$itemEntity->getProductPrice()),
                    new ProductQty($itemEntity->getQty())
                )
            );
        }

        return $cart;
    }

    public function toDoctrine(Cart $cart, ?CartDoctrineEntity $entity = null): CartDoctrineEntity
    {
        $entity ??= new CartDoctrineEntity();
        $entity->setCartCode($cart->getCode()->value());
        $entity->setCustomerId($cart->getCustomerId()?->value());
        $entity->setCreatedAt($cart->getCreatedAt());
        $entity->setUpdatedAt($cart->getUpdatedAt());
        $entity->setCheckedOutAt($cart->getCheckedOutAt());
        $entity->setStatus($cart->getStatus());

        $entity->getItems()->clear();

        $items = new ArrayCollection();
        foreach ($cart->getItems() as $item) {
            $itemEntity = new CartItemDoctrineEntity();
            $itemEntity->setCart($entity);
            $itemEntity->setProductId($item->getProductId()->value());
            $itemEntity->setProductName($item->getProductName()->value());
            $itemEntity->setProductPrice((string)$item->getProductPrice()->value());
            $itemEntity->setQty($item->getProductQty()->value());
            $itemEntity->setTotalPrice((string)$item->getTotalPrice());

            $items->add($itemEntity);
        }

        $entity->setItems($items);
        return $entity;
    }
}
