<?php


namespace CartBundle\Infrastructure\Persistence\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Psr\Log\LoggerInterface;

final class ElasticsearchClientFactory
{
    public static function build(string $host, ?LoggerInterface $logger = null, int $retries = 1): Client
    {
        $builder = ClientBuilder::create()
            ->setHosts([$host])
            ->setRetries(max(0, $retries));

        if (null !== $logger) {
            $builder->setLogger($logger);
        }

        return $builder->build();
    }
}
