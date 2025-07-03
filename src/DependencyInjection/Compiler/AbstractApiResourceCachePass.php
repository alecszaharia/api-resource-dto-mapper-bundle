<?php

declare(strict_types=1);

namespace Alecszaharia\ApiResourceDtoMapperBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

abstract class AbstractApiResourceCachePass implements CompilerPassInterface
{
    public function __construct(protected Finder $finder, protected string $path, protected string $varCachePath)
    {
    }

    abstract protected function processApiResourceProperty(\ReflectionProperty $property, ContainerBuilder $container): array;

    abstract protected function handleResultMap(array $resultMap, ContainerBuilder $container): void;

    public function process(ContainerBuilder $container)
    {
        $classList = $this->getApiResourceClassList($container);
        $cacheMap = [];
        foreach ($classList as $className) {
            $reflectionClass = new \ReflectionClass($className);

            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $map = $this->processApiResourceProperty($reflectionProperty, $container);

                if(count($map))
                {
                    $cacheMap[$className][$reflectionProperty->getName()] = $map;
                }
            }
        }

        $this->handleResultMap($cacheMap, $container);
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
