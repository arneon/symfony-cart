<?php

namespace CartBundle\Domain\Event;

final class OrderCreatedEvent
{
    public function __construct(
        public ?int $orderId,
        public readonly string $cartCode,
        public readonly float $total
    ) {}

    public function setOrderId(int $orderId): void
    {
        $this->orderId = $orderId;
    }
}
