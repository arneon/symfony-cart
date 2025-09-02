<?php

declare(strict_types=1);

namespace ProductBundle\Tests\Feature\UseCases;

use ProductBundle\Infrastructure\Persistence\Mysql\DoctrineProductRepository;
use ProductBundle\Tests\Feature\TestCase;

class UpdateProductTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        self::bootKernel();
        $this->runMigrations();
    }

    public function test_1_it_updates_a_product_successfully(): void
    {
        $headers = $this->authHeaders();
        $id = $this->createProduct($this->client, ['name' => 'Product 1' ]);

        $payload = $this->getProductPayload();
        $payload['name'] = 'Updated Product 11';
        $payload['id'] = $id;

        $this->client->request('PUT', '/api/products/'.$id, [], [], $headers, json_encode($payload));

        $this->assertResponseIsSuccessful();
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);

        $this->assertNotNull($product);
        $this->assertSame($payload['name'], $product->getName()->value());

        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'Product updated']),
            $this->client->getResponse()->getContent()
        );
    }

    public function test_2_it_tries_to_update_a_product_with_name_field_error(): void
    {
        $headers = $this->authHeaders();
        $id = $this->createProduct($this->client, ['name' => 'Product 2' ]);
        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $this->assertNotNull($product);

        $payload = $this->getProductPayload();
        $payload['name'] = '';

        $this->client->request('PUT', '/api/products/'.$id, [], [], $headers, json_encode($payload));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Name cannot be blank."]}', $this->client->getResponse()->getContent());
    }

    public function test_3_it_tries_to_update_a_product_with_name_and_price_fields_error(): void
    {
        $headers = $this->authHeaders();
        $id = $this->createProduct($this->client, ['name' => 'Product 3' ]);
        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $this->assertNotNull($product);

        $payload = $this->getProductPayload();
        $payload['name'] = '';
        $payload['price'] = -1;

        $this->client->request('PUT', '/api/products/'.$id, [], [], $headers, json_encode($payload));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Name cannot be blank.","Price must be greater than or equal to 0."]}', $this->client->getResponse()->getContent());
    }

    public function test_4_it_tries_to_update_a_product_with_name_price_and_stock_fields_error(): void
    {
        $headers = $this->authHeaders();
        $id = $this->createProduct($this->client, ['name' => 'Product 4' ]);
        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $this->assertNotNull($product);

        $payload = $this->getProductPayload();
        $payload['name'] = '';
        $payload['price'] = -1;
        $payload['stock'] = -1;

        $this->client->request('PUT', '/api/products/'.$id, [], [], $headers, json_encode($payload));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Name cannot be blank.","Price must be greater than or equal to 0.","Stock must be greater than or equal to 0."]}', $this->client->getResponse()->getContent());
    }

    public function test_5_it_tries_to_update_a_product_with_name_already_exists(): void
    {
        $headers = $this->authHeaders();
        $id = $this->createProduct($this->client, ['name' => 'Product 5' ]);
        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $firstProductName = $product->getName()->value();
        $this->assertNotNull($product);

        $payload = $this->getProductPayload();
        $payload['name'] = 'Product 6';
        $id = $this->createProduct($this->client, $payload);

        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product1 = $productRepository->find($id);
        $this->assertNotNull($product1);

        $payload = $this->getProductPayload();
        $payload['name'] = $firstProductName;

        $this->client->request('PUT', '/api/products/'.$id, [], [], $headers, json_encode($payload));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Name already exists."]}', $this->client->getResponse()->getContent());
    }
}
