<?php

namespace ProductBundle\Application\UseCases\FindAllProduct;

final readonly class ProductResponse
{
    public function __construct(
        public int    $id,
        public string $name,
        public float  $price,
        public int  $stock,
    )
    {
    }
}
