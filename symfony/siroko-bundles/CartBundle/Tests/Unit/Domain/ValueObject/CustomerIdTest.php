<?php

declare(strict_types=1);

namespace CartBundle\Tests\Unit\Domain\ValueObject;

use PHPUnit\Framework\TestCase;
use CartBundle\Domain\ValueObject\CustomerId;
use InvalidArgumentException;

class CustomerIdTest extends TestCase
{
    public function test_it_creates_customer_id_successfully(): void
    {
        $id = new CustomerId(1);
        $this->assertSame(1, $id->value());
    }

    public function test_it_throws_exception_when_customer_id_is_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CustomerId must be > 0.');
        new CustomerId(0);
    }

    public function test_it_throws_exception_when_customer_id_is_negative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CustomerId must be > 0.');
        new CustomerId(-5);
    }
}
