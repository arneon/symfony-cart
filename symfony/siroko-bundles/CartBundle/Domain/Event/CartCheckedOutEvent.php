<?php

namespace CartBundle\Domain\Event;

namespace CartBundle\Domain\Event;

final class CartCheckedOutEvent
{
    public function __construct(
        public readonly string $cartCode,
        public readonly int $orderId
    ) {}
}

