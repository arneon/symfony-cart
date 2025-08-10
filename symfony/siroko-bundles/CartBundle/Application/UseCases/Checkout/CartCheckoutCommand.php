<?php

namespace CartBundle\Application\UseCases\Checkout;

class CartCheckoutCommand {
    public function __construct(
        public readonly string $cartCode,
        public readonly string $cartTotal,
        public ?string $customerEmail = null,
        public ?string $customerId = null,
    ) {}
}
