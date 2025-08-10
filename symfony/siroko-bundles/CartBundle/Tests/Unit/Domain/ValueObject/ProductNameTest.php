<?php

declare(strict_types=1);

namespace CartBundle\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use CartBundle\Domain\ValueObject\ProductName;
use InvalidArgumentException;

class ProductNameTest extends TestCase
{
    public function test_it_creates_product_name_successfully(): void
    {
        $name = new ProductName('SRX M4 Oregon');
        $this->assertSame('SRX M4 Oregon', $name->value());
    }

    public function test_it_throws_exception_when_name_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name is required.');
        new ProductName('');
    }
}
