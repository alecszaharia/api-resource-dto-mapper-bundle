<?php

declare(strict_types=1);

namespace Alecszaharia\ApiResourceDtoMapperBundle\DependencyInjection\Compiler;
use App\Attribute\ValueFrom;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ValueFromCachePass extends AbstractApiResourceCachePass
{
    protected function processApiResourceProperty(\ReflectionProperty $property, ContainerBuilder $container): array
    {
        $valueFromAttrs = $property->getAttributes(ValueFrom::class);
        if (isset($valueFromAttrs[0]) && $attributeInstance = $valueFromAttrs[0]->newInstance()) {
            return [
                'callbackMethod' => $attributeInstance->callbackMethod,
            ];
        }

        return [];
    }

    protected function handleResultMap(array $resultMap, ContainerBuilder $container): void
    {
        $container->setParameter('resource_value_from_map', $resultMap);
    }
}
