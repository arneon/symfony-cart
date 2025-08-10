<?php

namespace CartBundle\Tests\Feature;

use CartBundle\Tests\TestCase as BaseTestCase;
use Symfony\Component\Process\Process;

class TestCase extends BaseTestCase
{
    protected function getProductPayload($name=null, $price=null, $stock=null): array
    {
        $time = time();
        return [
            'name' => $name ?? 'New Product '.$time,
            'price' => $price ?? random_int(100, 1000),
            'stock' => $stock ?? random_int(15, 35),
        ];
    }

    protected function createProduct($client, $payload = []): int
    {
        $payload = !empty($payload) ? $payload : $this->getProductPayload();
        $client->request('POST', '/api/products/', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode($payload));
        $data = json_decode($client->getResponse()->getContent(), true);

        return filter_var($data['id'], FILTER_VALIDATE_INT);
    }

    protected function runMigrations(): bool
    {
        $dbFile = dirname(__DIR__) . '/../../../var/data_test.db';
        if (file_exists($dbFile)) {
            unlink($dbFile);
        }

        $process = new Process([
            'php',
            'bin/console',
            'doctrine:schema:create',
            '--env=test',
            '--no-interaction'
        ]);
        $process->run();

        return $process->isSuccessful();
    }

}


