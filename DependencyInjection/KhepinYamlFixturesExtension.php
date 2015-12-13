<?php

namespace Khepin\YamlFixturesBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class KhepinYamlFixturesExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('khepin_yaml_fixtures.resources', $config['resources']);
        $container->setParameter('khepin_yaml_fixtures.directory', $config['directory']);

        // We set a method call even though we cannot add the proper reference yet
        // so that the compiler pass can add it afterwards
        if (isset($config['acl_manager'])) {
            $def = $container->getDefinition('khepin.yaml_loader');
            $def->addMethodCall('setAclManager', [null]);
        }
    }
}
