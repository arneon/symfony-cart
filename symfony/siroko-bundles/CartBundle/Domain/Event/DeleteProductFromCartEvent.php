<?php

namespace CartBundle\Domain\Event;

use CartBundle\Domain\ValueObject\CartCode;
use CartBundle\Domain\ValueObject\ProductId;
class DeleteProductFromCartEvent implements DomainEvent
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

    public static function eventName(): string
    {
        return 'cart.product_deleted';
    }

    public function toArray(): array
    {
        return [
            'cart_code' => $this->cartCode->value(),
            'product_id' => $this->productId->value(),
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }
}
