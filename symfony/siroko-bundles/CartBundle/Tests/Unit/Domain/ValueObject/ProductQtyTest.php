<?php

declare(strict_types=1);

namespace CartBundle\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use CartBundle\Domain\ValueObject\ProductQty;
use InvalidArgumentException;

class ProductQtyTest extends TestCase
{
    public function test_it_creates_product_qty_successfully(): void
    {
        $qty = new ProductQty(75);
        $this->assertSame(75, $qty->value());
    }

    public function test_it_throws_exception_when_qty_is_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Qty must be > 0.');
        new ProductQty(0);
    }

    public function test_it_throws_exception_when_qty_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Qty must be > 0.');
        new ProductQty(-5);
    }
}
