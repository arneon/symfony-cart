<?php

namespace ProductBundle\Application\UseCases\CreateProduct;

class CreateProductCommand
{
    public ?string $name;
    public ?float $price;
    public ?int $stock;

    public function __construct( ?string $name, ?float $price, ?int $stock )
    {
        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;
    }
}
