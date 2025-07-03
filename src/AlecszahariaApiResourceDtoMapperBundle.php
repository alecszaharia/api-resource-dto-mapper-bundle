<?php
namespace Alecszaharia\ApiResourceDtoMapperBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AlecszahariaApiResourceDtoMapperBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);


        $this->addCompilerPasses(new ResourceTransformCachePass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
            ->scalarNode('api_resource_folder')
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // the "$config" variable is already merged and processed so you can
        // use it directly to configure the service container (when defining an
        // extension class, you also have to do this merging and processing)
        $container->services()
            ->get('ResourceTransformCachePass')
            ->arg(0, $config['twitter']['client_id'])
            ->arg(1, $config['twitter']['client_secret'])
        ;
    }
}