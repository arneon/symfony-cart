<?php

namespace UserBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class UserExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $rootFolder = __DIR__ . '/../Resources/config/';
        $filesToLoad = [
            'services.yaml',
            'permissions.yaml',
        ];
        foreach ($filesToLoad as $file) {
            if (!file_exists($rootFolder.$file)) {
                die('Error: File does not exist '.$rootFolder.$file);
            }
        }

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        foreach ($filesToLoad as $file) {
            $loader->load($file);
        }
    }
}
