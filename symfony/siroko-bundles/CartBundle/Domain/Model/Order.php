<?php

namespace CartBundle\Domain\Model;

use CartBundle\Domain\Event\OrderCreatedEvent;
use CartBundle\Domain\Event\StockDecreaseRequested;
use App\Traits\DomainEventsTrait;
use DateTimeImmutable;

class Order
{
    use DomainEventsTrait;
    private ?int $id = null;
    public function __construct(
        private string $cartCode,
        private ?int $customerId,
        private ?string $customerEmail,
        private array $items,
        private float $total,
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        $this->recordEvent(new OrderCreatedEvent(null, $this->cartCode, $this->total));

        foreach ($this->items as $item) {
            $this->recordEvent(new StockDecreaseRequested(
                $item->getProductId()->value(),
                $item->getProductQty()->value(),
                null
            ));
        }
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->cartCode;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
        foreach ($this->domainEvents as $event) {
            if (method_exists($event, 'setOrderId')) {
                $event->setOrderId($id);
            }
        }
    }

    public function total(): float
    {
        return $this->total;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getItems(): array
    {
        return array_values($this->items);
    }

    public function addItem(OrderItem $item): void
    {
        foreach ($this->items as $existingItem) {
            if ((integer) $existingItem->getProductId()->value() === (integer) $item->getProductId()->value()) {
                throw new \LogicException('Product is already in the cart');
            }
        }

        $this->items[] = $item;
    }
}
