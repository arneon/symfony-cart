<?php

namespace ProductBundle\Infrastructure\Query\Redis;

use ProductBundle\Domain\Repository\ProductReadRepository as ReadRepository;
use Predis\Client;

class RedisProductQuery implements ReadRepository
{
    public function __construct(private Client $redis) {}

    public function getProduct(int $id): ?array
    {
        $data = $this->redis->get("product:{$id}");
        return $data ? json_decode($data, true) : null;
    }

    public function findAll(): array
    {
        $keys = $this->redis->keys("product:*");
        $result = [];

        foreach ($keys as $key) {
            $result[] = json_decode($this->redis->get($key), true);
        }

        return $result;
    }
}
