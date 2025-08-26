<?php

namespace App\MongoRepository\Library\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MongoField
{
    public function __construct(public ?string $name = null) {}
}
