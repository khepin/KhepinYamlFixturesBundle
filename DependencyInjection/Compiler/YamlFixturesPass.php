<?php

namespace Khepin\YamlFixturesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class YamlFixturesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('problematic.acl_manager')) {
            return;
        }
        // If there was a call registered to set the acl manager, we can now
        // set it with the proper reference
        $definition = $container->getDefinition('khepin.yaml_loader');
        if ($definition->hasMethodCall('setAclManager')) {
            $definition->removeMethodCall('setAclManager');
            $definition->addMethodCall('setAclManager', [new Reference('problematic.acl_manager')]);
        }
    }
}
