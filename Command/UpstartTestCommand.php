<?php

namespace SfNix\UpstartBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpstartTestCommand extends Base{

	protected function configure(){
		parent::configure();
		$this
			->setName('upstart:test')
			->setDescription('Example job.')
			->addOption('--exit', null, InputOption::VALUE_OPTIONAL, 'Exit after N seconds.')
			->addOption('--error', null, InputOption::VALUE_OPTIONAL, 'Throw exception after N seconds.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		parent::execute($input, $output);
		$logger = $this->getContainer()->get('logger');
		$startAt = time();
		$exitAt = $input->getOption('exit') ? $startAt + $input->getOption('exit') : null;
		$errorAt = $input->getOption('error') ? $startAt + $input->getOption('error') : null;
		while(true){
			sleep(1);
			$now = time();
			$logger->info('time', [$now - $startAt, 'seconds']);
			if($errorAt && $errorAt <= $now){
				$logger->info('error', []);
				throw new \Exception("Test exception.");
			}
			if($exitAt && $exitAt <= $now){
				$logger->info('exit', []);
				exit(0);
			}
		}
	}

}
