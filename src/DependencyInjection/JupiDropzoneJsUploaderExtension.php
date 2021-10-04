<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\DependencyInjection;

use Jupi\DropzoneJsUploaderBundle\Mapping\MappingReader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class JupiDropzoneJsUploaderExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config')
        );
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $service = $container->getDefinition(MappingReader::class);
        $service->replaceArgument(0, $config['mappings']);
    }
}
