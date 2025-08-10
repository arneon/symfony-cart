<?php

namespace CartBundle\Domain\ValueObject;

class ProductId
{
    public function __construct(private readonly int $value)
    {
        if ($value < 1) {
            throw new \InvalidArgumentException('ProductId must be > 0.');
        }
    }

    public function value(): int
    {
        return $this->value;
    }
}
