<?php

namespace App\Command;

use App\ElasticSearch\IndexBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ElasticCreateIndexCommand extends Command
{
    protected static $defaultName = 'elastic:create-index';

    private $indexBuilder;

    public function __construct(IndexBuilder $indexBuilder)
    {
        $this->indexBuilder = $indexBuilder;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Rebuild the Index.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->indexBuilder->create();

        $io->success('Index created!');
    }
}
