<?php

namespace Segakgd\MongoRepository\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MongoField
{
    public function __construct(public ?string $name = null) {}
}
