<?php

namespace App\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class PropertyMap
{
    public function __construct(public ?string $propertyName = null, public ?string $propertyType = null, public bool $identifier = false)
    {
    }
}
