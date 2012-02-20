<?php

if (!class_exists('PHPUnit_Framework_TestCase') ||
    version_compare(PHPUnit_Runner_Version::id(), '3.5') < 0
) {
    die('PHPUnit framework is required, at least 3.5 version');
}

if (!class_exists('PHPUnit_Framework_MockObject_MockBuilder')) {
    die('PHPUnit MockObject plugin is required, at least 1.0.8 version');
}

define('TESTS_PATH', __DIR__);
define('VENDOR_PATH', realpath(__DIR__ . '/../vendor'));

$classLoaderFile = VENDOR_PATH . '/Symfony/Component/ClassLoader/UniversalClassLoader.php';
if (!file_exists($classLoaderFile)) {
    die('cannot find vendor, run: php bin/vendors.php');
}
require_once $classLoaderFile;
$loader = new Symfony\Component\ClassLoader\UniversalClassLoader;
$loader->registerNamespaces(array(
    'Symfony'                    => VENDOR_PATH,
    'Doctrine\\Common'           => VENDOR_PATH.'/doctrine-common/lib',
    'Doctrine\\DBAL'             => VENDOR_PATH.'/doctrine-dbal/lib',
    'Doctrine\\ORM'              => VENDOR_PATH.'/doctrine-orm/lib',
    'Mockery'                    => VENDOR_PATH.'/Mockery/library',
    'Khepin'                     => __DIR__,
));

$loader->registerPrefixes(array(
    'Mockery'                        => VENDOR_PATH . '/Mockery/library',
));

$loader->register();

spl_autoload_register(function($class) {
    if (0 === strpos($class, 'Khepin\\YamlFixturesBundle\\')) {
        $path = __DIR__.'/../'.implode('/', array_slice(explode('\\', $class), 2)).'.php';
        if (!stream_resolve_include_path($path)) {
            return false;
        }
        require_once $path;
        return true;
    }
});

Doctrine\Common\Annotations\AnnotationRegistry::registerFile(
    VENDOR_PATH.'/doctrine-orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
);

$reader = new \Doctrine\Common\Annotations\AnnotationReader();
$reader = new \Doctrine\Common\Annotations\CachedReader($reader, new \Doctrine\Common\Cache\ArrayCache());
$_ENV['annotation_reader'] = $reader;

// Imports a class named appkernel to allow mocking it
require_once __DIR__.'/Khepin/Utils/AppKernel.php';