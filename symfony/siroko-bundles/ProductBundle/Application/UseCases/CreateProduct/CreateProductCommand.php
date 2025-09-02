<?php

namespace ProductBundle\Application\UseCases\CreateProduct;

class CreateProductCommand
{
    public ?string $name;
    public ?float $price;
    public ?int $stock;

    public function __construct(
        ?string $name = null,
        ?float $price = null,
        ?int $stock = null
    )
    {
        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;
    }
}
