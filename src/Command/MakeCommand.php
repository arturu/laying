<?php


namespace Arturu\Laying\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Arturu\Laying\Element;

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

    protected function createTemplate (InputInterface $input, OutputInterface $output){

        $output->writeln($input->getOption('name'));
    }


    protected function test (OutputInterface $output){

        $a = array(
            'type'=> 'div',
            'attributes'=> array("id"=>"idElement","class"=>"col-xs-12 region"), // see self::attributes
            'implicit'=> false, // true for implicit
            'content' => "{{ content }}", // tag content
        );

        $output->writeln( Element::element($a) );
    }
}