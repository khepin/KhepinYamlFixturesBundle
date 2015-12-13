<?php

namespace Khepin\YamlFixturesBundle;

use Khepin\YamlFixturesBundle\DependencyInjection\Compiler\YamlFixturesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KhepinYamlFixturesBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new YamlFixturesPass());
    }
}
