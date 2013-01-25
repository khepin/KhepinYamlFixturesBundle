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
            ->addArgument('context', InputArgument::OPTIONAL, 'Specify a context from which to load additional fixtures')
            ->addOption('purge-orm', null, InputOption::VALUE_NONE, 'If set, will purge the database before importing new fixtures')
            ->addOption('purge-mongodb', null, InputOption::VALUE_NONE, 'If set, will purge the database before importing new fixtures')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $context = $input->getArgument('context');
        if ($input->getOption('purge-orm')) {
            $this->getContainer()->get('khepin.yaml_loader')->purgeDatabase('orm');
        }
        if ($input->getOption('purge-mongodb')) {
            $this->getContainer()->get('khepin.yaml_loader')->purgeDatabase('mongodb');
        }

        $this->getContainer()->get('khepin.yaml_loader')->loadFixtures($context);

        $output->writeln('done!');
    }
}
