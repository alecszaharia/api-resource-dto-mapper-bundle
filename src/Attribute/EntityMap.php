<?php

namespace App\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class EntityMap
{
    public function __construct(public string $entityClass, public string $entityIdentifierColumn = 'id')
    {
    }
}
