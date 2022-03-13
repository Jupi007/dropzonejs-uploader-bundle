<?php

declare(strict_types=1);

namespace Jupi\DropzoneJsUploaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('jupi_dropzone_js_uploader');

        return $builder;
    }
}
