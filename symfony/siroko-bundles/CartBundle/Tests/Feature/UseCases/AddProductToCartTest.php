<?php

declare(strict_types=1);

namespace CartBundle\Tests\Feature\UseCases;

use CartBundle\Tests\Feature\TestCase;

class AddProductToCartTest extends TestCase
{
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
        self::bootKernel();
        $this->runMigrations();
    }

    public function test_1_it_adds_product_to_cart(): void
    {
        $productId = $this->createProduct($this->client, $this->getProductPayload());
        $this->assertIsInt($productId);

        $payload = [
            'cart_code' => null,
            'customer_id' => null,
            'product_id' => $productId,
            'qty'       => 2,
        ];

        $this->client->request('POST', '/api/carts/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertSame(201, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('cart_code', $data);
        $this->assertNotEmpty($data['cart_code']);

        $this->client->request('GET', '/api/carts/'.$data['cart_code']);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $cart = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('open', $cart['status']);
        $this->assertSame($data['cart_code'], $cart['cartCode']);
        $this->assertCount(1, $cart['items']);
        $this->assertSame($productId, $cart['items'][0]['productId']);
        $this->assertSame(2, $cart['items'][0]['qty']);
        $this->assertGreaterThan(0, $cart['total']);
    }

    public function test_2_it_adds_second_item_to_existing_open_cart(): void
    {
        $product_1 = $this->createProduct($this->client, $this->getProductPayload('New Product 1', 77, 7));
        $product_2 = $this->createProduct($this->client, $this->getProductPayload('New Product 2', 10.0, 5));

        $this->client->request('POST', '/api/carts/', [], [], ['CONTENT_TYPE'=>'application/json'], json_encode([
            'product_id' => $product_1,
            'qty' => 2
        ]));

        $this->assertSame(201, $this->client->getResponse()->getStatusCode());
        $cartCode = json_decode($this->client->getResponse()->getContent(), true)['cart_code'];

        $this->client->request('POST', "/api/carts/", [], [], ['CONTENT_TYPE'=>'application/json'], json_encode([
            'cart_code' => $cartCode,
            'product_id' => $product_2,
            'qty' => 1
        ]));

        $this->assertSame(201, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', "/api/carts/{$cartCode}");
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $cart = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $cart['items']);
        $this->assertSame('open', $cart['status']);
        $this->assertSame($cartCode, $cart['cartCode']);
    }

    public function test_3_it_tries_to_add_product_not_exists(): void
    {
        $this->client->request('POST', '/api/carts/', [], [], ['CONTENT_TYPE'=>'application/json'], json_encode([
            'product_id' => 999999,
            'qty' => 1
        ]));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $errors = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertContains('ProductId does not exist.', $errors['errors'] ?? []);
    }

    public function test_4_it_tries_to_add_product_qty_greater_than_stock(): void
    {
        $productId = $this->createProduct($this->client, $this->getProductPayload(stock: 3));

        $this->client->request('POST', '/api/carts/', [], [], ['CONTENT_TYPE'=>'application/json'], json_encode([
            'product_id' => $productId,
            'qty' => 4
        ]));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $errors = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertContains('Product quantity cannot be grater than the current stock quantity.', $errors['errors'] ?? []);
    }

    public function test_5_it_tries_to_add_same_product_in_cart_twice(): void
    {
        $product_1 = $this->createProduct($this->client, $this->getProductPayload());

        $this->client->request('POST', '/api/carts/', [], [], ['CONTENT_TYPE'=>'application/json'], json_encode([
            'product_id' => $product_1,
            'qty' => 1
        ]));
        $cartCode = json_decode($this->client->getResponse()->getContent(), true)['cart_code'];

        $this->client->request('POST', "/api/carts/", [], [], ['CONTENT_TYPE'=>'application/json'], json_encode([
            'cart_code' => $cartCode,
            'product_id' => $product_1,
            'qty' => 3
        ]));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $errors = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertContains('Product already exists in cart.', $errors['errors'] ?? []);
    }
}
