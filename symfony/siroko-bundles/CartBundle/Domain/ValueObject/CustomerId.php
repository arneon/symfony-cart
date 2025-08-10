<?php

namespace CartBundle\Domain\ValueObject;

class CustomerId
{
    private ?int $value;
    public function __construct(?int $value = null)
    {
        if ($value !== null && $value < 1) {
            throw new \InvalidArgumentException('CustomerId must be > 0.');
        }

        $this->value = $value;
    }

    public function value(): ?int
    {
        return $this->value;
    }
}
