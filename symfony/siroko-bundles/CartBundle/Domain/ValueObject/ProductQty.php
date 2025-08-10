<?php

namespace CartBundle\Domain\ValueObject;

class ProductQty
{
    public function __construct(private int $value)
    {
        if ($value < 1) {
            throw new \InvalidArgumentException("Qty must be > 0.");
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}
