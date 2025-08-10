<?php

declare(strict_types=1);

namespace CartBundle\Tests\Feature\UseCases;

use CartBundle\Tests\Feature\TestCase;

class CartCheckoutTest extends TestCase
{
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
        self::bootKernel();
        $this->runMigrations();
    }

    public function test_1_it_make_checkout_of_created_cart(): void
    {
        $productId = $this->createProduct($this->client, $this->getProductPayload());

        $payload = [
            'cart_code' => null,
            'customer_id' => null,
            'product_id' => $productId,
            'qty'       => 1,
        ];

        $this->client->request('POST', '/api/carts/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $cartCode = json_decode($this->client->getResponse()->getContent(), true)['cart_code'];
        $this->client->request('GET', "/api/carts/{$cartCode}", [], [], ['CONTENT_TYPE'=>'application/json']);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame($cartCode, $data['cartCode']);

        $checkoutPayload = [
            'cart_code' => $cartCode,
            'cart_total' => $data['total'],
            'customer_email' => "test@test.es",
        ];

        $this->client->request('POST', "/api/carts/checkout/", [], [], [
            'CONTENT_TYPE'=>'application/json'
        ], json_encode($checkoutPayload));

        $this->assertSame(201, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Cart checkout successful', $data['status']);
    }

    public function test_2_it_tries_to_make_checkout_of_cart_not_exists(): void
    {
        $productId = $this->createProduct($this->client, $this->getProductPayload());

        $payload = [
            'cart_code' => null,
            'customer_id' => null,
            'product_id' => $productId,
            'qty'       => 1,
        ];

        $this->client->request('POST', '/api/carts/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $cartCode = json_decode($this->client->getResponse()->getContent(), true)['cart_code'];
        $this->client->request('GET', "/api/carts/{$cartCode}", [], [], ['CONTENT_TYPE'=>'application/json']);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame($cartCode, $data['cartCode']);

        $checkoutPayload = [
            'cart_code' => 'XYZ',
            'cart_total' => $data['total'],
            'customer_email' => "test@test.es",
        ];

        $this->client->request('POST', "/api/carts/checkout/", [], [], [
            'CONTENT_TYPE'=>'application/json'
        ], json_encode($checkoutPayload));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());

        $errors = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertContains('CartCode does not exists.', $errors['errors'] ?? []);
    }

    public function test_3_it_tries_to_make_checkout_of_cart_with_bad_cart_total(): void
    {
        $productId = $this->createProduct($this->client, $this->getProductPayload());

        $payload = [
            'cart_code' => null,
            'customer_id' => null,
            'product_id' => $productId,
            'qty'       => 1,
        ];

        $this->client->request('POST', '/api/carts/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $cartCode = json_decode($this->client->getResponse()->getContent(), true)['cart_code'];
        $this->client->request('GET', "/api/carts/{$cartCode}", [], [], ['CONTENT_TYPE'=>'application/json']);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame($cartCode, $data['cartCode']);

        $checkoutPayload = [
            'cart_code' => $data['cartCode'],
            'cart_total' => ($data['total']+10),
            'customer_email' => "test@test.es",
        ];

        $this->client->request('POST', "/api/carts/checkout/", [], [], [
            'CONTENT_TYPE'=>'application/json'
        ], json_encode($checkoutPayload));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());

        $errors = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertContains('Cart total does not match. Please verify', $errors['errors'] ?? []);
    }
}
