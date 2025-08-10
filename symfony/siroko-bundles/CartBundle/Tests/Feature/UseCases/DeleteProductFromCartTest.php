<?php

declare(strict_types=1);

namespace CartBundle\Tests\Feature\UseCases;

use CartBundle\Tests\Feature\TestCase;

class DeleteProductFromCartTest extends TestCase
{
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
        self::bootKernel();
        $this->runMigrations();
    }

    private function addProductToCart() : array
    {
        $productId = $this->createProduct($this->client, $this->getProductPayload());

        $payload = [
            'cart_code' => null,
            'customer_id' => null,
            'product_id' => $productId,
            'qty'       => 2,
        ];

        $this->client->request('POST', '/api/carts/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $data = json_decode($this->client->getResponse()->getContent(), true);

        return [
            'cartCode' => $data['cart_code'],
            'productId' => $productId,
        ];
    }

    public function test_1_it_deletes_product_from_cart()
    {
        $cartData = $this->addProductToCart();

        $payload = [
            'cart_code' => $cartData['cartCode'],
            'product_id' => $cartData['productId'],
        ];

        $this->client->request('DELETE', '/api/carts/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function test_2_it_tries_to_delete_product_not_exists_from_cart()
    {
        $cartData = $this->addProductToCart();

        $payload = [
            'cart_code' => $cartData['cartCode'],
            'product_id' => ($cartData['productId'] + 1000),
        ];

        $this->client->request('DELETE', '/api/carts/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $errors = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertContains('ProductId does not exist.', $errors['errors'] ?? []);
    }

    public function test_3_it_tries_to_delete_product_from_cart_that_not_exists()
    {
        $cartData = $this->addProductToCart();

        $payload = [
            'cart_code' => 'XYZ',
            'product_id' => ($cartData['productId']),
        ];

        $this->client->request('DELETE', '/api/carts/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $errors = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertContains('CartCode does not exists.', $errors['errors'] ?? []);
    }
}
