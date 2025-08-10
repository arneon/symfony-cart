<?php

namespace CartBundle\Domain\Event;

final class OrderCreatedEvent
{
    public function __construct(
        public ?int $orderId,
        public readonly string $cartCode,
        public readonly float $total
    ) {}
}
