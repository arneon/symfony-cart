<?php

namespace CartBundle\Domain\Model;

use CartBundle\Domain\Event\ProductAddedToCartEvent;
use CartBundle\Domain\Event\CartCheckedOutEvent;
use CartBundle\Domain\ValueObject\CartCode;
use CartBundle\Domain\ValueObject\CustomerId;
use App\Traits\DomainEventsTrait;
use DateTimeImmutable;

class Cart
{
    use DomainEventsTrait;
    private ?int $id = null;
    private array $domainEvents = [];
    private CartCode $cartCode;
    private CustomerId $customerId;

    /**
     * @var CartItem[]
     */
    private array $items = [];

    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;
    private ?DateTimeImmutable $checkedOutAt = null;

    private string $status;

    /**
     * @var array<object>
     */
    private array $events = [];

    public function __construct(CartCode $cartCode, CustomerId $customerId)
    {
        $this->cartCode = $cartCode;
        $this->customerId = $customerId;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->status = 'open';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): CartCode
    {
        return $this->cartCode;
    }

    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCheckedOutAt(): ?DateTimeImmutable
    {
        return $this->checkedOutAt;
    }

    public function setId(int $id): void {
        $this->id = $id;

        if(!empty($this->items))
        {
            $this->recordEvent(new ProductAddedToCartEvent($this->cartCode, $this->items[0]->getProductId()));
        }

    }

    public function markAsCheckedOut(int $orderId): void
    {
        $this->status = 'checked_out';
        $this->checkedOutAt = new DateTimeImmutable();
        $this->touch();

        $this->recordEvent(new CartCheckedOutEvent($this->getCode()->value(), $orderId));
    }

    public function addItem(CartItem $item): void
    {
        foreach ($this->items as $existingItem) {
            if ($existingItem->getProductId()->value() === $item->getProductId()->value()) {
                throw new \LogicException('Product is already in the cart');
            }
        }

        $this->items[] = $item;
        $this->events[] = new ProductAddedToCartEvent($this->cartCode, $item->getProductId());
        $this->touch();
    }

    public function updateItemQuantity(int $productId, int $quantity): void
    {
        foreach ($this->items as $index => $item) {
            if ($item->getProductId()->value() === $productId) {
                if ($quantity < 1) {
                    unset($this->items[$index]);
                } else {
                    $item->setProductQty($quantity);
                }
                $this->touch();
                return;
            }
        }

        throw new \RuntimeException("Product $productId not found in cart");
    }

    public function removeItem(int $productId): void
    {
        foreach ($this->items as $index => $item) {
            if ($item->getProductId()->value() === $productId) {
                unset($this->items[$index]);
                $this->touch();
                return;
            }
        }

        throw new \RuntimeException("Product $productId not found in cart");
    }

    /**
     * @return CartItem[]
     */
    public function getItems(): array
    {
        return array_values($this->items);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function getTotal(): float
    {
        return array_reduce($this->items, fn($carry, CartItem $item) => $carry + $item->getTotalPrice(), 0.0);
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
