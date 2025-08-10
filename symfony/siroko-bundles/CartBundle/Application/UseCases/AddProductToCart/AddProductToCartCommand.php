<?php

namespace CartBundle\Application\UseCases\AddProductToCart;

class AddProductToCartCommand
{
    public function __construct(
        public readonly ?string $cartCode,
        public ?int $customerId,
        public readonly ?int $productId,
        public readonly ?int $qty
    ) {
        $this->customerId = !empty($customerId) ? $customerId : null;
    }
}
