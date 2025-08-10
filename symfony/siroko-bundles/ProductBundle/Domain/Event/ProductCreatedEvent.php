<?php

namespace ProductBundle\Domain\Event;

class ProductCreatedEvent
{
    public function __construct(public readonly int $productId) {}
}
