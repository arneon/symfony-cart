<?php

namespace ProductBundle\Domain\Model;

use ProductBundle\Domain\ValueObject\ProductName;
use ProductBundle\Domain\ValueObject\ProductPrice;
use ProductBundle\Domain\ValueObject\ProductStock;
use ProductBundle\Domain\Event\ProductUpdatedEvent;
use ProductBundle\Domain\Event\ProductCreatedEvent;


class Product
{
    private ?int $id = null;
    private array $domainEvents = [];

    public function __construct(
        private ProductName $name,
        private ProductPrice $price,
        private ProductStock $stock,
    ) {

    }

    public function recordEvent(object $event): void {
        $this->domainEvents[] = $event;
    }

    public function pullDomainEvents(): array {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ProductName
    {
        return $this->name;
    }

    public function getPrice(): ProductPrice
    {
        return $this->price;
    }

    public function getStock() : ProductStock
    {
        return $this->stock;
    }

    public function setId(int $id): void {
        $this->id = $id;
        $this->recordEvent(new ProductCreatedEvent($id));
    }

    public function setName(ProductName $newName): void {
        $this->name = $newName;
        $this->recordEvent(new ProductUpdatedEvent($this->id));
    }

    public function setPrice(ProductPrice $newPrice): void {
        $this->price = $newPrice;
        $this->recordEvent(new ProductUpdatedEvent($this->id));
    }

    public function setStock(ProductStock $stock): void {
        $this->stock = $stock;
        $this->recordEvent(new ProductUpdatedEvent($this->id));
    }
}
