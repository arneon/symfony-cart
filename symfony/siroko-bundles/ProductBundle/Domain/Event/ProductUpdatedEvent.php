<?php

namespace ProductBundle\Domain\Event;

class ProductUpdatedEvent
{
    public function __construct(public readonly int $productId) {}
}
