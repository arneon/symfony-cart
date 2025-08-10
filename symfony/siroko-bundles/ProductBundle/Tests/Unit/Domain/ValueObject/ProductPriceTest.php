<?php

declare(strict_types=1);

namespace ProductBundle\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use ProductBundle\Domain\ValueObject\ProductPrice;
use InvalidArgumentException;

class ProductPriceTest extends TestCase
{
    public function test_it_creates_product_price_successfully(): void
    {
        $price = new ProductPrice(109.95);
        $this->assertSame(109.95, $price->value());
    }

    public function test_it_allows_zero_as_valid_price(): void
    {
        $price = new ProductPrice(0.0);
        $this->assertSame(0.0, $price->value());
    }

    public function test_it_throws_exception_when_price_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Price must be >= 0.');
        new ProductPrice(-109.95);
    }
}
