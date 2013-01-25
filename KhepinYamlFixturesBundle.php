<?php

namespace Khepin\YamlFixturesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Khepin\YamlFixturesBundle\DependencyInjection\Compiler\YamlFixturesPass;

class KhepinYamlFixturesBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new YamlFixturesPass());
    }

}
