<?php

namespace ProductBundle\Domain\Event;

class ProductDeletedEvent
{
    public function __construct(public readonly int $productId) {}
}
