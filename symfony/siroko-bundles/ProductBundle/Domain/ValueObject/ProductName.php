<?php

namespace ProductBundle\Domain\ValueObject;

class ProductName
{
    public function __construct(private string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Product name is required.");
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
