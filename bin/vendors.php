#!/usr/bin/env php
<?php

// dependent libraries for test environment

define('VENDOR_PATH', __DIR__.'/../vendor');

if (!is_dir(VENDOR_PATH)) {
    mkdir(VENDOR_PATH, 0775, true);
}

$deps = [
    ['doctrine-orm', 'git://github.com/doctrine/doctrine2.git'],
    ['doctrine-dbal', 'git://github.com/doctrine/dbal.git'],
    ['doctrine-common', 'git://github.com/doctrine/common.git'],
    ['doctrine-data-fixtures', 'git://github.com/doctrine/data-fixtures.git'],

    ['Symfony/Component/ClassLoader', 'git://github.com/symfony/ClassLoader.git', 'v2.0.9'],
    ['Symfony/Component/Yaml', 'git://github.com/symfony/Yaml.git', 'v2.0.9'],

    ['Mockery', 'git://github.com/padraic/mockery'],
];

foreach ($deps as $dep) {
    list($name, $url, $rev) = $dep;

    echo "> Installing/Updating $name\n";

    $installDir = VENDOR_PATH.'/'.$name;
    if (!is_dir($installDir)) {
        system(sprintf('git clone %s %s', $url, $installDir));
    }

    system(sprintf('cd %s && git fetch origin && git reset --hard %s', $installDir, $rev));
}
