<?php

declare(strict_types=1);

namespace ProductBundle\Tests\Feature\UseCases;

use ProductBundle\Infrastructure\Persistence\Mysql\DoctrineProductRepository;
use ProductBundle\Tests\Feature\TestCase;

class DeleteProductTest extends TestCase
{
    public function test_0_it_runs_migrations(): void
    {
        $created = $this->runMigrations();
        $this->assertTrue($created);
    }

    public function test_1_it_deletes_a_product_successfully(): void
    {
        $client = static::createClient();
        $id = $this->createProduct($client);

        $productRepository = static::getContainer()->get(DoctrineProductRepository::class);
        $product = $productRepository->find($id);
        $this->assertNotNull($product);

        $client->request('DELETE', '/api/products/' . $id);

        $this->assertResponseIsSuccessful();
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['status' => 'Product deleted']),
            $client->getResponse()->getContent()
        );
    }

    public function test_2_it_tries_to_delete_a_product_that_id_not_exists(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/products/999');
        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Id does not exist."]}', $client->getResponse()->getContent());
    }

    public function test_3_it_tries_to_delete_a_product_with_id_error(): void
    {
        $client = static::createClient();
        $client->request('DELETE', '/api/products/9A9');
        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertSame('{"errors":["Id must be greater than 0."]}', $client->getResponse()->getContent());
    }

}
