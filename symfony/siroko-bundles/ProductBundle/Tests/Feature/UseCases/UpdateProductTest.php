<?php

declare(strict_types=1);

namespace ProductBundle\Tests\Feature\UseCases;

use ProductBundle\Infrastructure\Persistence\Mysql\DoctrineProductRepository;
use ProductBundle\Tests\Feature\TestCase;

class UpdateProductTest extends TestCase
{
    public function test_0_it_runs_migrations(): void
    {
        $created = $this->runMigrations();
        $this->assertTrue($created);
    }

    public function test_1_it_updates_a_product_successfully(): void
    {
        $client = static::createClient();
        $id = $this->createProduct($client, ['name' => 'Product 1' ]);
        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $this->assertNotNull($product);
        $productCurrentName = $product->getName()->value();

        $payload = $this->getProductPayload();
        $payload['name'] = 'Updated Product 1';
        $payload['id'] = $id;

        $client->request('PUT', '/api/products/'.$id, [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertResponseIsSuccessful();
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $this->assertNotNull($product);
        $this->assertSame($payload['name'], $product->getName()->value());
        $this->assertNotSame($productCurrentName, $product->getName()->value());

        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'Product updated']),
            $client->getResponse()->getContent()
        );
    }

    public function test_2_it_tries_to_update_a_product_with_name_field_error(): void
    {
        $client = static::createClient();
        $id = $this->createProduct($client, ['name' => 'Product 2' ]);
        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $this->assertNotNull($product);

        $payload = $this->getProductPayload();
        $payload['name'] = '';

        $client->request('PUT', '/api/products/'.$id, [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Name cannot be blank."]}', $client->getResponse()->getContent());
    }

    public function test_3_it_tries_to_update_a_product_with_name_and_price_fields_error(): void
    {
        $client = static::createClient();
        $id = $this->createProduct($client, ['name' => 'Product 3' ]);
        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $this->assertNotNull($product);

        $payload = $this->getProductPayload();
        $payload['name'] = '';
        $payload['price'] = -1;

        $client->request('PUT', '/api/products/'.$id, [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Name cannot be blank.","Price must be greater than or equal to 0."]}', $client->getResponse()->getContent());
    }

    public function test_4_it_tries_to_update_a_product_with_name_price_and_stock_fields_error(): void
    {
        $client = static::createClient();
        $id = $this->createProduct($client, ['name' => 'Product 4' ]);
        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $this->assertNotNull($product);

        $payload = $this->getProductPayload();
        $payload['name'] = '';
        $payload['price'] = -1;
        $payload['stock'] = -1;

        $client->request('PUT', '/api/products/'.$id, [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Name cannot be blank.","Price must be greater than or equal to 0.","Stock must be greater than or equal to 0."]}', $client->getResponse()->getContent());
    }

    public function test_5_it_tries_to_update_a_product_with_name_already_exists(): void
    {
        $client = static::createClient();
        $id = $this->createProduct($client, ['name' => 'Product 5' ]);
        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $firstProductName = $product->getName()->value();
        $this->assertNotNull($product);

        $payload = $this->getProductPayload();
        $payload['name'] = 'Product 6';
        $id = $this->createProduct($client, $payload);

        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product1 = $productRepository->find($id);
        $this->assertNotNull($product1);

        $payload = $this->getProductPayload();
        $payload['name'] = $firstProductName;

        $client->request('PUT', '/api/products/'.$id, [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Name already exists."]}', $client->getResponse()->getContent());
    }
}
