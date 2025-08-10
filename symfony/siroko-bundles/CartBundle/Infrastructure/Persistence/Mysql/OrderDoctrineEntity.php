<?php

namespace CartBundle\Infrastructure\Persistence\Mysql;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class OrderDoctrineEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'cart_code', type: 'string', length: 36, unique: true)]
    private string $cartCode;

    #[ORM\Column(name: 'cart_total', type: 'decimal', length: 20, scale: 6)]
    private string $cartTotal;

    #[ORM\Column(name: 'customer_email', type: 'string', length: 100)]
    private ?string $customerEmail;

    #[ORM\Column(name: 'customer_id', type: 'integer', nullable: true)]
    private ?int $customerId;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(
        mappedBy: 'order',
        targetEntity: OrderItemDoctrineEntity::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCartCode(): string
    {
        return $this->cartCode;
    }

    public function getCartTotal(): string
    {
        return $this->cartCode;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }

    public function setCartCode(string $cartCode): void
    {
        $this->cartCode = $cartCode;
    }

    public function setCartTotal(string $cartTotal): void
    {
        $this->cartTotal = $cartTotal;
    }

    public function setCustomerEmail(string $customerEmail): void
    {
        $this->customerEmail = $customerEmail;
    }

    public function setCustomerId(?int $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }
}
