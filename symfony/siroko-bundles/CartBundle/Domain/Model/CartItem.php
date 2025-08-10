<?php

namespace CartBundle\Domain\Model;

use CartBundle\Domain\ValueObject\ProductId;
use CartBundle\Domain\ValueObject\ProductName;
use CartBundle\Domain\ValueObject\ProductPrice;
use CartBundle\Domain\ValueObject\ProductQty;

class CartItem
{
    private ProductId $productId;
    private ProductName $productName;
    private ProductPrice $productPrice;
    private ProductQty $qty;

    public function __construct(
        ProductId $productId,
        ProductName $productName,
        ProductPrice $productPrice,
        ProductQty $qty
    ) {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->productPrice = $productPrice;
        $this->qty = $qty;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getProductName(): ProductName
    {
        return $this->productName;
    }

    public function getProductPrice(): ProductPrice
    {
        return $this->productPrice;
    }

    public function getProductQty(): ProductQty
    {
        return $this->qty;
    }

    public function getTotalPrice(): float
    {
        return $this->getProductPrice()->value() * $this->getProductQty()->value();
    }

    public function setProductQty(int $newQty): void
    {
        $this->qty = new ProductQty($newQty);
    }

    public function setProductPrice(float $newPrice): void
    {
        $this->productPrice = new ProductPrice($newPrice);
    }

    public function updateNameAndPrice(ProductName $name, ProductPrice $price): void
    {
        $this->productName = $name;
        $this->productPrice = $price;
    }
}
