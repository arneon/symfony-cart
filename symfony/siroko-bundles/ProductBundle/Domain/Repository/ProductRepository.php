<?php

namespace ProductBundle\Domain\Repository;

use ProductBundle\Domain\Model\Product;

interface ProductRepository
{
    public function find(int $id): ?Product;
    public function save(Product $product): int;
    public function update(Product $product): int;
    public function delete(Product $product): bool;
}
