<?php


namespace Arturu\Laying\Command;

use Arturu\Laying\Laying;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class RenderCommand extends Command
{

    protected function configure()
    {
        $this
            // the name of the Command (the part after "bin/console")
            ->setName('render')

            // the short description shown while running "php bin/console list"
            ->setDescription('Render layout')

            ->addArgument('path', InputArgument::REQUIRED, 'Path of file YAML')

            // the full Command description shown when running the Command with
            // the "--help" option
            ->setHelp('This Command allows you to render layout page.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pathLayout = $input->getArgument('path');

        $laying = new Laying($pathLayout);

        $output->writeln( $laying->renderLayout() );
    }
}