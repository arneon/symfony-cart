<?php

namespace CartBundle\Domain\Event;

use CartBundle\Domain\ValueObject\CartCode;

final class CartCheckedOutEvent implements DomainEvent
{
    private CartCode $cartCode;
    private int $orderId;
    private \DateTimeImmutable $occurredAt;
    public function __construct(
        CartCode $cartCode,
        int $orderId
    ) {
        $this->cartCode = $cartCode;
        $this->orderId = $orderId;
        $this->occurredAt = new \DateTimeImmutable();
    }

    public function getCartCode(): CartCode
    {
        return $this->cartCode;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public static function eventName(): string
    {
        return 'cart.checked_out';
    }

    public function toArray(): array
    {
        return [
            'cart_code' => $this->cartCode->value(),
            'order_id' => $this->getOrderId(),
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }
}

