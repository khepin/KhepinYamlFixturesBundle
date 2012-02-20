<?php

namespace Khepin\Tests;

use \Mockery as m;
use Khepin\YamlFixturesBundle\Loader\YamlLoader;
use Khepin\Utils\BaseTestCaseOrm;

class YamlFixtureTest extends BaseTestCaseOrm {

    public function setUp() {
        $this->getDoctrine();
    }

    public function testSomething() {
        $kernel = m::mock('alias:AppKernel');
        $loader = new YamlLoader($kernel, $this->doctrine, array('SomeBundle'));
    }
}