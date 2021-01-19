<?php
namespace Silverstripe\PingdomCLI;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Silverstripe\Pingdom\Api;
use Symfony\Component\Console\Helper\Table;

class GetChecks extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'get-checks';
    private $pingdomToken;
    protected function configure()
    {
      $this->addOption('json')
      ->addArgument('api-key',InputArgument::OPTIONAL,'your oauth token','');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    if (strlen($input->getArgument('api-key')) > 0) {
        $this->pingdomToken = $input->getArgument('api-key');
    }else if (getenv('PINGDOM_API_TOKEN')){
        $this->pingdomToken = getenv('PINGDOM_API_TOKEN');
    }else{
        $output->writeln('No authentication token provided');
        $output->writeln('exiting');
        return 1;
    }
      $pingdom = new Api($this->pingdomToken);
      $checks = $pingdom->getChecks();
      if($input->getOption('json')){
        $output->writeln(\json_encode($checks));
        return 0;
      }else{
        if (count($checks) > 0) {
            $table = new Table($output);
            $table
            ->setHeaders(['Name', 'Hostname', 'Status']);
            foreach ($checks as $check) {
                $table->addRow([$check->name, $check->hostname, $check->status]);
            }
            $table->render();
            return 0;
        }
        else {
            $output->writeln('no data from pingdom api');
            return 1;
        }
      }
    }
}

