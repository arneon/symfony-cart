<?php

namespace CartBundle\Application\UseCases\DeleteProductFromCart;

class DeleteProductFromCartCommand
{
    public function __construct(
        public readonly ?string $cartCode,
        public readonly ?int $productId,
    ) {}
}

