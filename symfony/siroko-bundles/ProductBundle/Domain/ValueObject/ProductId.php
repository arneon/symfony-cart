<?php

namespace ProductBundle\Domain\ValueObject;

class ProductId
{
    public function __construct(private readonly int $value) {}

    public function value(): int
    {
        return $this->value;
    }
}
