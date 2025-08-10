<?php

namespace CartBundle\Domain\Event;

final class StockDecreaseRequested
{
    public function __construct(
        public readonly int $productId,
        public readonly int $qty,
        public ?int $orderId = null
    ) {}

    public function setOrderId(int $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->qty;
    }
}
