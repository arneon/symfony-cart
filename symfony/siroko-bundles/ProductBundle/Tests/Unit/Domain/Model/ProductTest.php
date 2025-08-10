<?php

declare(strict_types=1);

namespace ProductBundle\Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use ProductBundle\Domain\Model\Product;
use ProductBundle\Domain\ValueObject\ProductName;
use ProductBundle\Domain\ValueObject\ProductPrice;
use ProductBundle\Domain\ValueObject\ProductStock;
use ProductBundle\Domain\Event\ProductUpdatedEvent;

class ProductTest extends TestCase
{
    private function createProduct($name = 'SRX M4 Oregon', $price = 109.95, $stock = 75): Product
    {
        return new Product(
            new ProductName($name),
            new ProductPrice($price),
            new ProductStock($stock)
        );
    }
    public function test_it_can_be_created(): void
    {
        $product = $this->createProduct();

        $this->assertSame('SRX M4 Oregon', $product->getName()->value());
        $this->assertSame(109.95, $product->getPrice()->value());
        $this->assertSame(75, $product->getStock()->value());
    }

    public function test_it_sets_name_and_records_event(): void
    {
        $product = $this->createProduct();
        $product->setId(1);

        $product->setName(new ProductName('Z2'));
        $events = $product->pullDomainEvents();

        $this->assertCount(2, $events);
        $this->assertInstanceOf(ProductUpdatedEvent::class, $events[1]);
        $this->assertSame(1, $events[0]->productId);
    }

    public function test_it_sets_price_and_records_event(): void
    {
        $product = $this->createProduct();
        $product->setId(2);

        $product->setPrice(new ProductPrice(20));
        $events = $product->pullDomainEvents();

        $this->assertCount(2, $events);
        $this->assertInstanceOf(ProductUpdatedEvent::class, $events[1]);
        $this->assertSame(2, $events[0]->productId);
    }

    public function test_it_sets_stock_and_records_event(): void
    {
        $product = $this->createProduct();
        $product->setId(3);

        $product->setStock(new ProductStock(99));
        $events = $product->pullDomainEvents();

        $this->assertCount(2, $events);
        $this->assertInstanceOf(ProductUpdatedEvent::class, $events[1]);
        $this->assertSame(3, $events[0]->productId);
    }

    public function test_pull_domain_events_clears_the_list(): void
    {
        $product = $this->createProduct();
        $product->setId(4);
        $product->setName(new ProductName('Z2'));

        $this->assertCount(2, $product->pullDomainEvents());
        $this->assertCount(0, $product->pullDomainEvents());
    }
}
