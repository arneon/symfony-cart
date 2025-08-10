<?php

declare(strict_types=1);

namespace CartBundle\Tests\Unit\Domain\ValueObject;

use CartBundle\Domain\Exception\ValidationException;
use PHPUnit\Framework\TestCase;
use CartBundle\Domain\ValueObject\CartCode;

class CartCodeTest extends TestCase
{
    public function test_it_creates_cart_code_successfully(): void
    {
        $cartCode = new CartCode('123456789012345678901234567890123456');
        $this->assertSame('123456789012345678901234567890123456', $cartCode->value());
    }

    public function test_it_throws_exception_when_cart_code_is_empty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cart code is required.');
        new CartCode('');
    }
}
