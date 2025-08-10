<?php

namespace ProductBundle\Domain\ValueObject;

class ProductStock
{
    public function __construct(private int $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException("Stock must be >= 0.");
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}
