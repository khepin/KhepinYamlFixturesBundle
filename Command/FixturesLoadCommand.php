<?php

namespace Khepin\YamlFixturesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixturesLoadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('khepin:yamlfixtures:load')
            ->setDescription('Loads all fixtures in a given context')
            ->addArgument(
                'context',
                InputArgument::OPTIONAL,
                'Specify a context from which to load additional fixtures'
            )
            ->addOption(
                'purge-orm',
                null,
                InputOption::VALUE_NONE,
                'If set, will purge the database before importing new fixtures'
            )
            ->addOption(
                'database-name',
                null,
                InputArgument::OPTIONAL,
                'If set, will purge the database specified. If not set, will purge all
                databases. (require purge option)'
            )
            ->addOption(
                'purge-mongodb',
                null,
                InputOption::VALUE_NONE,
                'If set, will purge the database before importing new fixtures'
            )
            ->addOption(
                'purge-with-truncate',
                null,
                InputOption::VALUE_NONE,
                'Purge data by using a database-level TRUNCATE statement (only for ORM)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $context = $input->getArgument('context');
        if ($input->getOption('purge-orm')) {
            $this->getContainer()->get('khepin.yaml_loader')->purgeDatabase(
                'orm',
                $input->getOption('database-name'),
                $input->getOption('purge-with-truncate')
            );
        }
        if ($input->getOption('purge-mongodb')) {
            $this->getContainer()->get('khepin.yaml_loader')->purgeDatabase(
                'mongodb',
                $input->getOption('database-name')
            );
        }

        $this->getContainer()->get('khepin.yaml_loader')->loadFixtures($context);

        $output->writeln('done!');
    }
}
