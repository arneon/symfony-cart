<?php

declare(strict_types=1);

namespace CartBundle\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use CartBundle\Domain\ValueObject\CartId;
use InvalidArgumentException;

class CartIdTest extends TestCase
{
    public function test_it_creates_cart_id_successfully(): void
    {
        $id = new CartId(1);
        $this->assertSame(1, $id->value());
    }

    public function test_it_throws_exception_when_cart_id_is_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CartId must be > 0.');
        new CartId(-1);
    }

    public function test_it_throws_exception_when_cart_id_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CartId must be > 0.');
        new CartId(-5);
    }
}
