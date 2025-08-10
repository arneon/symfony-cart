<?php

namespace CartBundle\Application\UseCases\UpdateCartProductQty;

class UpdateCartProductQtyCommand
{
    public function __construct(
        public readonly string $cartCode,
        public readonly int $productId,
        public readonly int $qty
    ) {}
}
