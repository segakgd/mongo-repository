<?php

namespace Segakgd\MongoRepository;

use Dotenv\Dotenv;
use MongoDB\Client;
use MongoDB\Collection;

readonly class MongoCollectionManager
{
    private string $uri;
    private string $database;

    public function __construct(?string $uri = null, ?string $database = null)
    {
        if ($uri === null || $database === null) {
            if (class_exists(Dotenv::class)) {
                $dotenvPath = dirname(__DIR__, 3);

                if (file_exists($dotenvPath . '/.env')) {
                    Dotenv::createImmutable($dotenvPath)->safeLoad();
                }
            }
        }

        $this->uri = $uri ?? ($_ENV['MONGODB_URL'] ?? getenv('MONGODB_URL') ?? 'mongodb://localhost:27017');
        $this->database = $database ?? ($_ENV['MONGODB_DB'] ?? getenv('MONGODB_DB') ?? 'test');
    }

    public function connection(string $collectionName): Collection
    {
        $client = new Client($this->uri);

        return $client->selectCollection(
            databaseName: $this->database,
            collectionName: $collectionName,
        );
    }
}
