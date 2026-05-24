<?php

namespace Segakgd\MongoRepository;

use Segakgd\MongoRepository\Attributes\MongoField;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONDocument;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionType;

abstract class AbstractMongoModel
{
    #[MongoField]
    public ?ObjectId $_id = null;

    #[MongoField]
    public ?UTCDateTime $createdAt = null;

    #[MongoField]
    public ?UTCDateTime $updatedAt = null;

    /**
     * @throws ReflectionException
     */
    public static function make(array $data): static
    {
        $reflection = new ReflectionClass(static::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($reflection->getProperties() as $property) {
            $attr = $property->getAttributes(MongoField::class)[0] ?? null;
            $key = $attr?->newInstance()?->name ?? $property->getName();

            if (array_key_exists($key, $data)) {
                $property->setAccessible(true);
                $value = $data[$key];

                $type = $property->getType();

                if ($type && static::isArrayType($type) && $value instanceof BSONDocument) {
                    $value = json_decode(json_encode($value), true);
                }

                $property->setValue($instance, $value);
            }
        }

        return $instance;
    }

    private static function isArrayType(?ReflectionType $type): bool
    {
        if ($type === null) {
            return false;
        }

        // Если это union type
        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $subType) {
                if ($subType instanceof ReflectionNamedType && $subType->getName() === 'array') {
                    return true;
                }
            }
            return false;
        }

        // Если простой
        if ($type instanceof ReflectionNamedType) {
            return $type->getName() === 'array';
        }

        return false;
    }

    public function toData(): array
    {
        $result = [];
        $reflection = new ReflectionClass($this);

        foreach ($reflection->getProperties() as $property) {
            $attr = $property->getAttributes(MongoField::class)[0] ?? null;
            $key = $attr?->newInstance()?->name ?? $property->getName();

            $property->setAccessible(true);
            $value = $property->getValue($this);

            // Не включаем _id, если оно null
            if ($key === '_id' && $value === null) {
                continue;
            }

            if ($key === 'createdAt' && $value === null) {
                $value = new UTCDateTime();
            }

            if ($key === 'updatedAt') {
                $value = new UTCDateTime();
            }

            $result[$key] = $value;
        }

        return $result;
    }

    abstract public static function getCollectionName(): string;
}
