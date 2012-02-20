This bundles provides you with a way to use YAML based fixtures for symfony2 and
Doctrine2. Basic features are already implemented and usable but more are coming.

**Travic CI status:** [![Build Status](https://secure.travis-ci.org/khepin/KhepinYamlFixturesBundle.png?branch=master)](http://travis-ci.org/khepin/KhepinYamlFixturesBundle)

# Installation

Through the deps files add:

    [KhepinYamlFixturesBundle]
        git=https://github.com/khepin/KhepinYamlFixturesBundle.git
        target=/bundles/Khepin/YamlFixturesBundle

Run your vendor script `./bin/vendors install`.

In your `autoload.php` register the Khepin namespace:

    $loader->registerNamespaces(array(
        // ...
        'Khepin'           => __DIR__.'/../vendor/bundles',
        // ...
    ));

Then register the bundle in `AppKernel.php` it's better to only register it in 
the dev environment as it shouldn't be needed elsewhere.

    public function registerBundles()
    {
        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            //...
            $bundles[] = new Khepin\YamlFixturesBundle\KhepinYamlFixturesBundle(),
            //...
        }
    }

# Configuration

In your `config.yml` or `config_dev.yml` add the following:

    khepin_yaml_fixtures:
        resources:
            - MyBundle
            - MyOtherBundle

Under 'resources' is a list of the bundles that have fixtures that you wish to 
load. The fixtures will be loaded in that order.

# Define your fixture files

It is important to note that unlike in Symfony1, the order in which you load your
fixtures DOES MATTER. There's 2 ways you can manipulate that order for now:

- In the configuration: to decide which bundles have their fixtures loaded first
- On the fixture file names: inside of each bundle, fixture files are loaded in 
alphabetical order

Fixture files all go under `MyBundle/DataFixtures/somefixtures.yml`.

You can only define fixtures for one class per file.

Fixture files are to be written in this format:

    model: Name\Space\MyBundle\Entity\User
    tags: [ test, dev, prod ] # optional parameter
    save_in_reverse: false # optional parameter
    fixtures:
        michael:
            name: Michael
            phonenumber: 8765658
            birthday: "1989-12-12"

You can use references to previously created fixtures:

    model: Name\Space\MyBundle\Entity\Car
    fixtures:
        audi_a3:
            owner: michael
            since: "2010-12-12" #dates NEED to be set inside quotes

You can also define as many files as you want for the same entity. This will be
useful when used together with context tags (see below).

# Usage

## From the command line

    php app/console khepin:yamlfixtures:load <context>

More later regarding contexts, there is no need to add a context unless you have
a reason to.

**ATTENTION**: the command line can only load one context at a time for now.

## From anywhere else

If you need to load the fixtures from anywhere else like say ... your functional
tests in order to setup a clean database for testing, you can access the same thing
through the service container with the added advantage of being able to load
multiple contexts together:

    $container->get('khepin.yaml_loader')->loadFixtures('prod', 'test', ...);

## About contexts

Sometimes when setting up fixtures for testing purpose, you need to have different
configurations. This is what the context aims to help solving.

The contexts are equivalent to the tags set in the fixture file under the tag key.
You can set as many tags as you want on a fixture file. Such as `prod`, `test` etc...

If you define fixtures in this way, then from the command line, calling:

    php app/console khepin:yamlfixtures:load prod

All the fixture files for which you have set:
    
    tags: [ ..., prod, ... ]

Will be loaded. This way you can define fixtures that are loaded whenever you use
a test or dev environment but are not loaded in prod for example.

A fixture file with no tags at all is **always** loaded! This way you can setup your
bootstrapping fixtures in files that have absolutely no tags and then have fixtures
specific for each purpose.

## And what the heck is this "save_in_reverse" thingy?

This parameter can be omitted most of the time. It's only useful so far when you
have a self referencing table. For example if you had fixtures like this:

    fixtures:
        last_level:
            next_level: none
            name: Meet the boss
        middle_level:
            next_level: last_level
            name: complete the quest
        start_level:
            next_level: middle_level
            name: introduction

In this case, we need to put `last_level` first in our fixtures since it's the only
one that doesn't reference anything else. We could not create `start_level` first
because it needs `middle_level` to already exist etc...

The problem with this is that when purging the database, the ORMPurger() goes through
rows one by one ordered by their ids. So if we save them in this order, `last_level`
should be the first to go away which will cause a problems with foreign keys as it is
still referenced by `middle_level`.

Save in reverse will create the objects in this order so the references are set
properly and then save them in the opposite order so there is no exception when 
purging the database.

## Using ACLs

If you need to set ACL entries on your fixtures, it is possible. The ACLs are
created after all fixtures have been saved so that there is no possible conflict.

To set ACLs for the fixtures, you need to be using 
[ProblematicAclManagerBundle](problematic/ProblematicAclManagerBundle).

And to update your configuratin as follows:

    khepin_yaml_fixtures:
        acl_manager: ~
        resources:
            - MyBundle
            - MyOtherBundle

The ACLs can only use the standard defined masks from the Symfony MaskBuilder.
Example:

    model: My\NameSpace\Car
    tags: [ test ]
    fixtures:
      dad_car:
        name: Mercedes
      mom_car:
        name: Mini Cooper
        
    acl:
      dad_car:
        user_dad: MASK_OWNER
        user_mom: MASK_MASTER
      mom_car:
        user_mom: MASK_OWNER

Be careful that the ACLs in Symfony are not managed through Doctrine and 
therefore will not be purged when you re-create your fixtures. However if 
any conflicts, loading the ACLs will overwrite all previous ACL entries.

# Limitations

- The ordering of file loading might not be sufficient YET for people who need 
to load from Bundle A then B and then A again.
- There is no support for mongodb or couchdb. As I personally use mongo on some
projects, this will come at some point.
- It probably has a lot of bugs and edge cases that have not been tested yet as 
I did not encounter them so far.
