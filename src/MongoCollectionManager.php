<?php

namespace App\MongoRepository\Library;

use MongoDB\Client;
use MongoDB\Collection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class MongoCollectionManager
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
    ) {}

    public function connection(string $collectionName): Collection
    {
        $client = new Client(
            uri: $this->parameterBag->get('MONGODB_URL'),
        );

        return $client->selectCollection(
            databaseName: $this->parameterBag->get('MONGODB_DB'),
            collectionName: $collectionName,
        );
    }
}
