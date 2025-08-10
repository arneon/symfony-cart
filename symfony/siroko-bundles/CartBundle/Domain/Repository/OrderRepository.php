<?php

namespace CartBundle\Domain\Repository;

use CartBundle\Domain\Model\Order;

interface OrderRepository
{
    public function save(Order $order): int;
    public function existsByCartCode(string $cartCode): bool;
}
