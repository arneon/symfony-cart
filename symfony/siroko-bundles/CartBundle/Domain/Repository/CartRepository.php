<?php

namespace CartBundle\Domain\Repository;

use CartBundle\Domain\Model\Cart;
use CartBundle\Domain\ValueObject\CartId;
use CartBundle\Domain\ValueObject\CartCode;
use CartBundle\Domain\ValueObject\ProductId;

interface CartRepository
{
    public function findByCode(CartCode $code): ?Cart;
    public function find(?CartId $id): ?Cart;
    public function findOpenCartsWithProduct(ProductId $productId): array;
    public function save(Cart $cart): int;
    public function delete(CartCode $cartCode): bool;
}
