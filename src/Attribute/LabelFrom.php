<?php

namespace App\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class LabelFrom
{
    /**
     * @warning Make sure you use the existent property name. It may silently return unexpected results.
     */
    public function __construct(public string $association, public ?string $fromProperty = null, public ?string $fromMethod = null)
    {
    }
}
