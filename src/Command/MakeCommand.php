<?php


namespace Arturu\Laying\Command;

use Arturu\Laying\Laying;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class MakeCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the Command (the part after "bin/console")
            ->setName('make')

            // the short description shown while running "php bin/console list"
            ->setDescription('Render template')

            ->addOption(
                'type',
                null,
                InputOption::VALUE_OPTIONAL,
                'Operation type: template|test. Use --type=test for test make command',
                'template'
            )

            ->addOption(
                'name',
                null,
                InputOption::VALUE_OPTIONAL,
                'Template name',
                null
            )

            // the full Command description shown when running the Command with
            // the "--help" option
            ->setHelp('This Command allows you to render template page.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operationType = $input->getOption('type');

        if ($operationType=='template'){
            $this->createTemplate($input,$output);
        }

        if ($operationType=='test'){
            $this->test($output);
        }
    }

    protected function createTemplate (InputInterface $input, OutputInterface $output)
    {

        $output->writeln($input->getOption('name'));
    }


    protected function test (OutputInterface $output)
    {
        $laying = new Laying(
            __DIR__.'/../../pages/page.conf.yml',
            __DIR__.'/../../pages/page.yml'
        );

        $output->writeln( $laying->renderLayout() );
    }
}