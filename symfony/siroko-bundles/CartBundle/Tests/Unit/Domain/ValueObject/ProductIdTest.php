<?php

declare(strict_types=1);

namespace CartBundle\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use CartBundle\Domain\ValueObject\ProductId;
use InvalidArgumentException;

class ProductIdTest extends TestCase
{
    public function test_it_creates_product_id_successfully(): void
    {
        $id = new ProductId(75);
        $this->assertSame(75, $id->value());
    }

    public function test_it_throws_exception_when_product_id_is_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ProductId must be > 0.');
        new ProductId(-1);
    }

    public function test_it_throws_exception_when_product_id_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ProductId must be > 0.');
        new ProductId(-5);
    }
}
