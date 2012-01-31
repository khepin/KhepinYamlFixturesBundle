<?php
namespace Khepin\YamlFixturesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixturesLoadCommand extends ContainerAwareCommand {
    protected function configure()
    {
        $this
            ->setName('khepin:yamlfixtures:load')
            ->setDescription('Loads all fixtures in a given context')
            ->addArgument('context', InputArgument::OPTIONAL, 'Specify a context from which to load additional fixtures')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $context = $input->getArgument('context');
        
        $this->getContainer()->get('khepin.yaml_loader')->loadFixtures($context);

        $output->writeln('done!');
    }
}