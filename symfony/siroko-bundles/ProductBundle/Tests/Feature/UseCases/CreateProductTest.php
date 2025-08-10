<?php

declare(strict_types=1);

namespace ProductBundle\Tests\Feature\UseCases;

use ProductBundle\Infrastructure\Persistence\Mysql\DoctrineProductRepository;
use ProductBundle\Tests\Feature\TestCase;

class CreateProductTest extends TestCase
{
    public function test_0_it_runs_migrations(): void
    {
        $created = $this->runMigrations();
        $this->assertTrue($created);
    }

    public function test_1_it_creates_a_product_successfully(): void
    {
        $client = static::createClient();
        $payload = $this->getProductPayload();

        $client->request('POST', '/api/products/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $data = json_decode($client->getResponse()->getContent(), true);
        $id = $data['id'];

        $this->assertResponseIsSuccessful();
        $this->assertSame(201, $client->getResponse()->getStatusCode());

        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $this->assertNotNull($product);
        $this->assertSame($payload['name'], $product->getName()->value());

        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'Product created', 'id' => $id]),
            $client->getResponse()->getContent()
        );
    }

    public function test_2_it_tries_to_create_a_product_with_empty_name(): void
    {
        $client = static::createClient();
        $payload = $this->getProductPayload();
        $payload['name'] = '';

        $client->request('POST', '/api/products/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Name cannot be blank."]}', $client->getResponse()->getContent());
    }

    public function test_3_it_tries_to_create_a_product_with_empty_name_and_invalid_price(): void
    {
        $client = static::createClient();
        $payload = $this->getProductPayload();
        $payload['name'] = '';
        $payload['price'] = -1;

        $client->request('POST', '/api/products/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Name cannot be blank.","Price must be greater than or equal to 0."]}', $client->getResponse()->getContent());
    }

    public function test_4_it_tries_to_create_a_product_with_empty_name_invalid_price_and_stock(): void
    {
        $client = static::createClient();
        $payload = $this->getProductPayload();
        $payload['name'] = '';
        $payload['price'] = -1;
        $payload['stock'] = -1;

        $client->request('POST', '/api/products/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Name cannot be blank.","Price must be greater than or equal to 0.","Stock must be greater than or equal to 0."]}', $client->getResponse()->getContent());
    }

    public function test_5_it_tries_to_create_a_product_with_name_already_exists(): void
    {
        $client = static::createClient();
        $payload = $this->getProductPayload();
        $payload['name'] = 'Product 1';
        $id = $this->createProduct($client, $payload);

        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product1 = $productRepository->find($id);
        $this->assertNotNull($product1);

        $payload = $this->getProductPayload();
        $payload['name'] = $product1->getName()->value();

        $client->request('POST', '/api/products/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Name already exists."]}', $client->getResponse()->getContent());
    }
}
