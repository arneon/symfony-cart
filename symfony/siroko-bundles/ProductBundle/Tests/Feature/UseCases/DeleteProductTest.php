<?php

declare(strict_types=1);

namespace ProductBundle\Tests\Feature\UseCases;

use ProductBundle\Infrastructure\Persistence\Mysql\DoctrineProductRepository;
use ProductBundle\Tests\Feature\TestCase;

class DeleteProductTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        self::bootKernel();
        $this->runMigrations();
    }

    public function test_1_it_deletes_a_product_successfully(): void
    {
        $headers = $this->authHeaders();
        $id = $this->createProduct($this->client);

        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $this->assertNotNull($product);

        $this->client->request('DELETE', '/api/products/' . $id, [], [], $headers);

        $this->assertResponseIsSuccessful();
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'Product deleted']),
            $this->client->getResponse()->getContent()
        );
    }

    public function test_2_it_tries_to_delete_a_product_that_id_not_exists(): void
    {
        $headers = $this->authHeaders();
        $this->client->request('DELETE', '/api/products/999', [], [], $headers);
        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Id does not exist."]}', $this->client->getResponse()->getContent());
    }

    public function test_3_it_tries_to_delete_a_product_with_id_error(): void
    {
        $headers = $this->authHeaders();
        $this->client->request('DELETE', '/api/products/9A9', [], [], $headers);
        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Id must be greater than 0."]}', $this->client->getResponse()->getContent());
    }
}
