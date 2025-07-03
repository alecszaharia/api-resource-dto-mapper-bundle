<?php

declare(strict_types=1);

namespace Alecszaharia\ApiResourceDtoMapperBundle\DependencyInjection\Compiler;

use App\Attribute\LabelFrom;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FromLabelCachePass extends AbstractApiResourceCachePass
{
    protected function processApiResourceProperty(\ReflectionProperty $property, ContainerBuilder $container): array
    {
        $labelFromAttrs = $property->getAttributes(LabelFrom::class);
        if (isset($labelFromAttrs[0]) && $attributeInstance = $labelFromAttrs[0]->newInstance()) {
            return [
                'association' => $attributeInstance->association,
                'fromProperty' => $attributeInstance->fromProperty,
                'fromMethod' => $attributeInstance->fromMethod,
            ];
        }

        return [];
    }

    protected function handleResultMap(array $resultMap, ContainerBuilder $container): void
    {
        $container->setParameter('resource_read_labels_map', $resultMap);
    }

}
