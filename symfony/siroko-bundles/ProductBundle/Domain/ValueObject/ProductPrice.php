<?php

namespace ProductBundle\Domain\ValueObject;

class ProductPrice
{
    public function __construct(private float $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException("Price must be >= 0.");
        }
    }

    public function value(): float
    {
        return $this->value;
    }
}
