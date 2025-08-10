<?php

declare(strict_types=1);

namespace CartBundle\Tests\Unit\Domain\Model;

use CartBundle\Domain\Event\ProductAddedToCartEvent;
use CartBundle\Domain\ValueObject\CustomerId;
use PHPUnit\Framework\TestCase;
use CartBundle\Domain\Model\Cart;
use CartBundle\Domain\Model\CartItem;
use CartBundle\Domain\ValueObject\ProductId;
use CartBundle\Domain\ValueObject\CartId;
use CartBundle\Domain\ValueObject\CartCode;
use CartBundle\Domain\ValueObject\ProductName;
use CartBundle\Domain\ValueObject\ProductPrice;
use CartBundle\Domain\ValueObject\ProductQty;
use ProductBundle\Domain\Model\Product;

class CartTest extends TestCase
{
    private function createCart($cartCode = '1234567890', $customerId = null): Cart
    {
        return new Cart(
            new CartCode($cartCode),
            new CustomerId($customerId),
        );
    }

    private function addProductToCart(int $productId = 1, string $productName = 'Product test', float $price = 10.0, int $qty = 1): CartItem
    {
        return new CartItem(
            new ProductId($productId),
            new ProductName($productName),
            new ProductPrice($price),
            new ProductQty($qty),
        );
    }

    public function test_1_it_can_be_created(): void
    {
        $cart = $this->createCart();

        $this->assertSame('1234567890', $cart->getCode()->value());
        $this->assertSame('open', $cart->getStatus());
        $this->assertNull($cart->getCheckedOutAt());
        $this->assertTrue($cart->isEmpty());
        $this->assertSame(0.0, $cart->getTotal());
        $this->assertInstanceOf(\DateTimeImmutable::class, $cart->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $cart->getUpdatedAt());
    }

    public function test_2_it_product_can_be_added_to_cart(): void
    {
        $cart = $this->createCart('12345678901234567890');
        $before = $cart->getUpdatedAt()->getTimestamp();

        $cart->addItem($this->addProductToCart());

        $this->assertCount(1, $cart->getItems());
        $this->assertGreaterThanOrEqual($before, $cart->getUpdatedAt()->getTimestamp());
    }

    public function test_3_it_duplicated_product_cannot_be_added_to_cart(): void
    {
        $cart = $this->createCart('123456789012345678901');
        $cart->addItem($this->addProductToCart(10));
        $this->assertCount(1, $cart->getItems());

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Product is already in the cart');
        $cart->addItem($this->addProductToCart(10));
    }

    public function test_4_it_update_cart_product_quantity(): void
    {
        $cart = $this->createCart('1234567890123456789012');
        $cart->addItem($this->addProductToCart(10, 'Product 1', 10.0, 1));
        $cart->updateItemQuantity(10, 3);

        $this->assertSame(3, $cart->getItems()[0]->getProductQty()->value());
        $this->assertEquals(30.0, $cart->getTotal());

        $cart->updateItemQuantity(10, 0);
        $this->assertTrue($cart->isEmpty());
    }

    public function test_5_it_update_cart_quantity_of_product_that_not_exists(): void
    {
        $cart = $this->createCart('1234567890123456789012');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product 99 not found in cart');
        $cart->updateItemQuantity(99, 2);
    }

    public function test_6_it_delete_from_cart_product_that_not_exists(): void
    {
        $cart = $this->createCart('1234567890123456789012');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product 99 not found in cart');
        $cart->removeItem(99);
    }

    public function test_7_get_cart_total(): void
    {
        $cart = $this->createCart();
        $cart->addItem($this->addProductToCart(5, 'Product 5 Test', 7.5, 4));
        $cart->addItem($this->addProductToCart(6, 'Product 6 Test', 10.0, 2));

        $this->assertEquals(50, $cart->getTotal());
    }

    public function test_8_emit_event(): void
    {
        $cart = $this->createCart();
        $cart->addItem($this->addProductToCart(7));

        if (method_exists($cart, 'pullDomainEvents')) {
            $cart->pullDomainEvents();
        }

        $cart->setId(100);

        $events = $cart->pullDomainEvents();
        $this->assertNotEmpty($events);
        $this->assertInstanceOf(ProductAddedToCartEvent::class, $events[0]);
    }
}
