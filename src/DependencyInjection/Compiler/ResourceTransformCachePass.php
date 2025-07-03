<?php

declare(strict_types=1);

namespace Alecszaharia\ApiResourceDtoMapperBundle\DependencyInjection\Compiler;

use App\Attribute\EntityMap;
use App\Attribute\PropertyMap;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

class ResourceTransformCachePass implements CompilerPassInterface
{
    /**
     * AbstractAnnotationCachePass constructor.
     */
    public function __construct(protected Finder $finder, protected string $path, protected string $varCachePath)
    {
    }

    public function process(ContainerBuilder $container)
    {
        $classList = $this->getApiResourceClassList($container);
        $resourceEntityMap = [];
        $entityResourceMap = [];
        $propertyIRIMap = [];
        foreach ($classList as $className) {
            $reflectionClass = new \ReflectionClass($className);
            $attributes = $reflectionClass->getAttributes(EntityMap::class);
            if (count($attributes)) {
                /**
                 * @var EntityMap $attributeInstance ;
                 */
                $attributeInstance = $attributes[0]->newInstance();

                if (!class_exists($attributeInstance->entityClass)) {
                    throw new \RuntimeException('Entity class not found in TransformMap attribute on class '.$className);
                }

                $entityReflectionClass = new \ReflectionClass($attributeInstance->entityClass);
                $entityIdentifierProperty = $attributeInstance->entityIdentifierColumn;

                $propertyMap = [];

                $resourcePropertyIdentifier = $entityIdentifierProperty;
                foreach ($reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
                    $propAttributes = $reflectionProperty->getAttributes(PropertyMap::class);
                    foreach ($propAttributes as $propAttribute) {
                        /**
                         * @var PropertyMap $propertyMapInstance ;
                         */
                        $propertyMapInstance = $propAttribute->newInstance();
                        $entityPropertyName = $propertyMapInstance->propertyName ?? $reflectionProperty->getName();
                        $entityPropertyType = $propertyMapInstance->propertyType;
                        $reflectionEntityProperty = $entityReflectionClass->getProperty($entityPropertyName);

                        $reflectionIntersectionType = $reflectionEntityProperty->getType()?->getName() ?? $entityPropertyType;

                        if (is_null($reflectionIntersectionType)) {
                            throw new \RuntimeException('The entity property '.$entityPropertyName.' does not have a type hint.');
                        }

                        $propertyMap[$reflectionProperty->getName()] = [
                            'resourcePropertyType' => $reflectionProperty->getType()->getName(),
                            'entityProperty' => $reflectionEntityProperty->getName(),
                            'entityPropertyType' => $reflectionIntersectionType,
                        ];

                        if ($propertyMapInstance->identifier) {
                            $resourcePropertyIdentifier = $propertyMapInstance->propertyName;
                        }
                    }

//                    $propAttributes = $reflectionProperty->getAttributes(PropertyReference::class);
//                    foreach ($propAttributes as $propAttribute) {
//                        $propertyMap[$reflectionProperty->getName()]['returnIRI'] = true;
//                    }

//                    $propAttributes = $reflectionProperty->getAttributes(ResourceIRI::class);
//                    if (count($propAttributes)) {
//                        $propertyIRIMap[$className] = $reflectionProperty->getName();
//                    }
                }

                $mapdata = [
                    'entityClass' => $attributeInstance->entityClass,
                    'propertyMap' => $propertyMap,
                    'resourcePropertyIdentifier' => $resourcePropertyIdentifier,
                ];

                $resourceEntityMap[$className] = $mapdata;

                if (isset($entityResourceMap[$attributeInstance->entityClass])) {
                    throw new \RuntimeException('You have declared two ApiResources mapped to the same entity ['.$attributeInstance->entityClass.']');
                }

                $entityResourceMap[$attributeInstance->entityClass] = $className;
            }
        }
        $container->setParameter('resource_entity_map', $resourceEntityMap);
        $container->setParameter('entity_resource_map', $entityResourceMap);
        $container->setParameter('resource_iri_property_map', $propertyIRIMap);

        if ($this->varCachePath && file_exists($this->varCachePath) && is_writable($this->varCachePath)) {
            file_put_contents($this->varCachePath.'/resource_entity_map.json', json_encode($resourceEntityMap));
            file_put_contents($this->varCachePath.'/entity_resource_map.json', json_encode($entityResourceMap));
            file_put_contents($this->varCachePath.'/resource_iri_property_map.json', json_encode($propertyIRIMap));
        }
    }

    /**
     * @param array $excludePatterns
     *
     * @return array
     */
    protected function getApiResourceClassList(ContainerBuilder $container, $excludePatterns = [])
    {
        $classes = [];

        $this->finder
            ->files()
            ->name('*.php')
            ->in($this->path);

        if (!$this->finder->hasResults()) {
            return $classes;
        }

        $cwd = $container->getParameter('kernel.project_dir');

        foreach ($this->finder as $file) {
            $path = $file->getPathname();
            $path = str_replace([$cwd, '.php', '/'], ['', '', '\\'], $path);

            // replace psr-4
            $className = str_replace('src\\', 'App\\', ltrim($path, '\\'));

            if (class_exists($className)) {
                $classes[] = $className;
            }
        }

        return $classes;
    }
}
