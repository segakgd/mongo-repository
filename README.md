# mongo-repository

Пакет добавляет обёртку в виде репозиториев и моделей, для работы с mongo data base.
В основе лежит стандартный пакет для mongodb (https://github.com/mongodb/mongo-php-library)

---
## Установка

```bash
composer require segakgd/mongo_repository
```

---

## Пример использования

### Описываем UserModel

```php

class UserModel extends AbstractMongoModel
{
    #[MongoField]
    public string $name;
    
    #[MongoField]
    public int $companyId;

    public static function getCollectionName(): string
    {
        return 'user';
    }
}
```

### Описываем репозиторий для модели UserModel

```php
<?php

use Segakgd\MongoRepository\AbstractMongoModel;
use Segakgd\MongoRepository\AbstractMongoRepository;
use Segakgd\MongoRepository\Attributes\MongoField;
use Segakgd\MongoRepository\MongoCollectionManager;

/**
 * @method UserModel|null   find(string $oid)
 * @method array<UserModel> findAll()
 * @method array<UserModel> findBy(array $criteria = [], array $options = [])
 * @method UserModel|null   findOneBy(array $criteria = [], array $options = [])
 * @method int              count(array $criteria = [])
 * @method UserModel        create(UserModel $model)
 * @method UserModel        update(UserModel $model)
 * @method void             delete(UserModel $model)
 */
class UserRepository extends AbstractMongoRepository
{
    public function __construct(MongoCollectionManager $manager)
    {
        parent::__construct($manager, UserModel::class);
    }
}
```

### Используем

```php

$connection = new MongoCollectionManager(
    'mongodb://root:password@localhost:27017', // подключение
    'mongo_db' // название базы
);

$repository = new UserRepository($connection);
$companyId = 999;

$data = $repository->findOneBy(['companyId' => $companyId]);

var_dump($data->toData());

```

## Подключение в Symfony

```yaml
Segakgd\MongoRepository\MongoCollectionManager:
    arguments:
        $uri: '%env(MONGODB_URL)%'
        $database: '%env(MONGODB_DB)%'
```
