This bundles provides you with a way to use YAML based fixtures for symfony2 and
Doctrine2. Basic features are already implemented and usable but more are coming.

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

# Usage

## From the command line

    php app/console khepin:yamlfixtures:load <context>

More later regarding contexts, there is no need to add a context unless you have
a reason to.

## From anywhere else

If you need to load the fixtures from anywhere else like say ... your functional
tests in order to setup a clean database for testing, you can access the same thing
through the service container:

    $container->get('khepin.yaml_loader')->loadFixtures($context);

## About contexts

Sometimes when setting up fixtures for testing purpose, you need to have different
configurations. This is what the context aims to help solving.

For now this is how your fixture files are organized:

    src/
        namespace/
            MyBundle/
                DataFixtures/
                    somefixtures.yml
                    otherfixtures.yml
            MyOtherBundle/
                DataFixtures/
                    somefixtures.yml
                    otherfixtures.yml

The files under DataFixtures will always be loaded in the database, no matter what 
the context is. But you can set some fixtures to be loaded only in a given context
like this:

    src/
        namespace/
            MyBundle/
                DataFixtures/
                    somefixtures.yml
                    otherfixtures.yml
                    testjapanese/
                        japanesefixtures.yml
                    testgerman/
                        germanfixtures.yml
            MyOtherBundle/
                DataFixtures/
                    somefixtures.yml
                    otherfixtures.yml
                    testjapanese/
                        fixturesforjapantest.yml

If you define fixtures in this way, then from the command line, calling:

    php app/console khepin:yamlfixtures:load testjapanese

All the fixture files under a `testjapanese` subfolder of the DataFixtures folder
will be loaded as well. If you use `testgerman`, it will load the german fixtures
where they exist. Once again, fixtures that are not in a subdirectory of `DataFixtures`
are *always* loaded.


# Limitations

- The ordering of file loading might not be sufficient YET for people who need 
to load from Bundle A then B and then A again.
- There is no support for mongodb or couchdb. As I personally use mongo on some
projects, this will come at some point.
- It probably has a lot of bugs and edge cases that have not been tested yet as 
I did not encounter them so far.