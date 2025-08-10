<?php

namespace ProductBundle\Application\UseCases\DeleteProduct;

class DeleteProductCommand
{
    public function __construct(public readonly mixed $id)
    {
    }
}
