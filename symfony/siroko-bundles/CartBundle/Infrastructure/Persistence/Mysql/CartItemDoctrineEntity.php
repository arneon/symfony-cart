<?php

namespace CartBundle\Infrastructure\Persistence\Mysql;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cart_items')]
class CartItemDoctrineEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: CartDoctrineEntity::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'cart_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private CartDoctrineEntity $cart;

    #[ORM\Column(name: 'product_id', type: 'integer')]
    private int $productId;

    #[ORM\Column(name: 'product_name', type: 'string', length: 255)]
    private string $productName;

    #[ORM\Column(name: 'product_price', type: 'decimal', precision: 20, scale: 6)]
    private string $productPrice;

    #[ORM\Column(type: 'integer')]
    private int $qty;

    #[ORM\Column(name: 'total_price', type: 'decimal', precision: 20, scale: 6)]
    private string $totalPrice;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getCart(): CartDoctrineEntity
    {
        return $this->cart;
    }

    public function setCart(CartDoctrineEntity $cart): void
    {
        $this->cart = $cart;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): void
    {
        $this->productName = $productName;
    }

    public function getProductPrice(): string
    {
        return $this->productPrice;
    }

    public function setProductPrice(string $productPrice): void
    {
        $this->productPrice = $productPrice;
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function setQty(int $qty): void
    {
        $this->qty = $qty;
    }

    public function getTotalPrice(): string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(string $totalPrice): void
    {
        $this->totalPrice = $totalPrice;
    }
}
