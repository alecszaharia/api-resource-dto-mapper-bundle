<?php

namespace App\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ValueFrom
{
    public function __construct(public string $callbackMethod)
    {
    }
}
