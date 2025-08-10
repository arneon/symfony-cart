<?php

namespace ProductBundle\Application\UseCases\UpdateProduct;

class UpdateProductCommand
{
    public function __construct(
        public readonly mixed    $id,
        public readonly string $name,
        public readonly float  $price,
        public readonly int  $stock,
    )
    {
    }
}
