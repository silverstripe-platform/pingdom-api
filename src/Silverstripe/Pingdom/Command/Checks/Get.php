<?php

namespace Silverstripe\Pingdom\Command\Checks;

use Silverstripe\Pingdom\Api\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Get extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'checks:get';
    private $pingdomToken;

    protected function configure()
    {
        $this->setDescription('get all pingdom checks attached to the provided account token')
        ->addOption('json')
        ->addArgument('api-key', InputArgument::OPTIONAL, 'your oauth token', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (strlen($input->getArgument('api-key')) > 0) {
            $this->pingdomToken = $input->getArgument('api-key');
        } elseif (getenv('PINGDOM_API_TOKEN')) {
            $this->pingdomToken = getenv('PINGDOM_API_TOKEN');
        } else {
            $output->writeln('No authentication token provided');
            $output->writeln('exiting');

            return Command::FAILURE;
        }
        $pingdom = new Client($this->pingdomToken);
        $checks = $pingdom->getChecks();
        if ($input->getOption('json')) {
            $output->writeln(\json_encode($checks));

            return Command::SUCCESS;
        }
        if (count($checks) > 0) {
            $table = new Table($output);
            $table
            ->setHeaders(['Name', 'Hostname', 'Status']);
            foreach ($checks as $check) {
                $table->addRow([$check->name, $check->hostname, $check->status]);
            }
            $table->render();

            return Command::SUCCESS;
        }

        $output->writeln('no data from pingdom api');

        return Command::FAILURE;
    }
}
