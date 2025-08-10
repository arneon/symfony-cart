<?php

namespace CartBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class CartExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $file = __DIR__ . '/../Resources/config/services.yaml';
        if (!file_exists($file)) {
            die('Error: File does not exist '.$file);
        }

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');
    }
}
