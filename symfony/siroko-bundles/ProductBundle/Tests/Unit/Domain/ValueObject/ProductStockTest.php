<?php

declare(strict_types=1);

namespace ProductBundle\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use ProductBundle\Domain\ValueObject\ProductStock;
use InvalidArgumentException;

class ProductStockTest extends TestCase
{
    public function test_it_creates_product_stock_successfully(): void
    {
        $stock = new ProductStock(75);
        $this->assertSame(75, $stock->value());
    }

    public function test_it_allows_zero_stock(): void
    {
        $stock = new ProductStock(0);
        $this->assertSame(0, $stock->value());
    }

    public function test_it_throws_exception_when_stock_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Stock must be >= 0.');
        new ProductStock(-5);
    }
}
