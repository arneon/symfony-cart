<?php

namespace CartBundle\Application\UseCases\CartView;

use CartBundle\Domain\Repository\CartRepository;
use CartBundle\Domain\ValueObject\CartCode;
use CartBundle\Application\DTO\CartMapper;

final readonly class CartViewHandler
{
    public function __construct(
        private CartRepository $repository,
        private CartViewValidator $validator,
        private CartMapper $mapper,
    )
    {
    }

    public function __invoke(string $cartCode): array
    {
        $this->validator->validate($cartCode);
        $cart = $this->repository->findByCode(new CartCode($cartCode));
        return $this->mapper->toArray($cart);
    }
}
