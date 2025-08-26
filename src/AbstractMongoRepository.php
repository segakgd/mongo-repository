<?php

namespace App\MongoRepository\Library;

use InvalidArgumentException;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;

abstract class AbstractMongoRepository
{
    protected Collection $collection;

    public function __construct(
        MongoCollectionManager $manager,
        protected readonly string $modelClass,
    ) {
        if (!method_exists($modelClass, 'getCollectionName')) {
            throw new InvalidArgumentException("Class {$modelClass} must implement static method getCollectionName()");
        }

        $collectionName = $modelClass::getCollectionName();

        if (!is_string($collectionName) || trim($collectionName) === '') {
            throw new InvalidArgumentException("Collection name returned from {$modelClass}::getCollectionName() is invalid");
        }

        $this->collection = $manager->connection(
            collectionName: $collectionName,
        );
    }

    public function find(string $oid): ?AbstractMongoModel
    {
        $oid = new ObjectId($oid);

        /** @var BSONDocument|null $data */
        $data = $this->collection->findOne(
            [
                '_id' => $oid,
            ]
        );

        if (!is_null($data)) {
            return $this->makeModel($data);
        }

        return null;
    }

    public function findAll(): array
    {
        return $this->collection->find()->toArray();
    }

    public function findBy(array $criteria = [], array $options = []): array
    {
        $cursor = $this->collection->find($criteria, $options);

        $models = [];

        foreach ($cursor as $document) {
            $models[] = $this->makeModel($document);
        }

        return $models;
    }

    public function findOneBy(array $criteria = [], array $options = []): ?AbstractMongoModel
    {
        /** @var BSONDocument|null $document */
        $document = $this->collection->findOne($criteria, $options);

        if ($document !== null) {
            return $this->makeModel($document);
        }

        return null;
    }

    public function count(array $criteria = []): int
    {
        return $this->collection->countDocuments($criteria);
    }

    public function create(AbstractMongoModel $model): AbstractMongoModel
    {
        $data = $model->toData();

        $result = $this->collection->insertOne($data);

        $model->_id = $result->getInsertedId();

        return $model;
    }

    public function update(AbstractMongoModel $model): AbstractMongoModel
    {
        $data = $model->toData();

        $this->collection->updateOne(
            [
                '_id' => $model->_id,
            ],
            [
                '$set' => $data,
            ]
        );

        return $model;
    }

    public function delete(AbstractMongoModel $model): void
    {
        $this->collection->deleteOne(
            [
                '_id' => $model->_id,
            ],
        );
    }

    private function makeModel(BSONDocument $document): AbstractMongoModel
    {
        /** @var AbstractMongoModel $model */
        $model = $this->modelClass;

        return $model::make($document->getArrayCopy());
    }
}
