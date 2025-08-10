<?php

namespace CartBundle\Infrastructure\Persistence\Mysql\Mapper;

use CartBundle\Domain\Model\Order;
use CartBundle\Domain\Model\OrderItem;
use CartBundle\Domain\ValueObject\CartCode;
use CartBundle\Domain\ValueObject\CartId;
use CartBundle\Domain\ValueObject\CustomerId;
use CartBundle\Domain\ValueObject\ProductId;
use CartBundle\Domain\ValueObject\ProductName;
use CartBundle\Domain\ValueObject\ProductPrice;
use CartBundle\Domain\ValueObject\ProductQty;
use CartBundle\Infrastructure\Persistence\Mysql\OrderDoctrineEntity;
use CartBundle\Infrastructure\Persistence\Mysql\OrderItemDoctrineEntity;
use Doctrine\Common\Collections\ArrayCollection;

class OrderMapper
{
    public function toDomain(OrderDoctrineEntity $entity): Order
    {
        $order = new Order(
            new CartCode($entity->getCartCode()),
            new CustomerId($entity->getCustomerId())
        );

        $orderId = $entity->getId();
        if($orderId)
        {
            $order->setId($orderId);

        }

        $reflection = new \ReflectionClass($order);
        foreach (['createdAt'] as $prop) {
            $property = $reflection->getProperty($prop);
            $property->setAccessible(true);
            $property->setValue($order, $entity->{'get' . ucfirst($prop)}());
        }

        foreach ($entity->getItems() as $itemEntity) {
            $order->addItem(
                new OrderItem(
                    new ProductId($itemEntity->getProductId()),
                    new ProductName($itemEntity->getProductName()),
                    new ProductPrice((float)$itemEntity->getProductPrice()),
                    new ProductQty($itemEntity->getQty())
                )
            );
        }

        return $order;
    }

    public function toDoctrine(Order $order, ?OrderDoctrineEntity $entity = null): OrderDoctrineEntity
    {
        $entity ??= new OrderDoctrineEntity();
        $entity->setCartCode($order->getCode());
        $entity->setCustomerId($order->getCustomerId());
        $entity->setCustomerEmail($order->getCustomerEmail());
        $entity->setCreatedAt($order->getCreatedAt());
        $entity->setCartTotal($order->total());

        $entity->getItems()->clear();

        $items = new ArrayCollection();
        foreach ($order->getItems() as $item) {
            $itemEntity = new OrderItemDoctrineEntity();
            $itemEntity->setOrder($entity);
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
