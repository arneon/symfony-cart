<?php

namespace CartBundle\Domain\Event;

use CartBundle\Domain\ValueObject\CartCode;
use CartBundle\Domain\ValueObject\CartId;
use CartBundle\Domain\ValueObject\ProductId;
class ProductAddedToCartEvent
{
    private CartCode $cartCode;
    private ProductId $productId;
    private \DateTimeImmutable $occurredAt;
    public function __construct(CartCode $cartCode, ProductId $productId)
    {
        $this->cartCode = $cartCode;
        $this->productId = $productId;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getCartCode(): CartCode
    {
        return $this->cartCode;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
