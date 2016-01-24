<?php

namespace SfNix\UpstartBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpstartStopCommand extends Base{

	protected function configure(){
		parent::configure();
		$this
			->setName('upstart:stop')
			->setDescription('Stop jobs. Use job names and tags as filter. Apply to all jobs if no filters are specified.')
			->addOption('no-wait', null, InputOption::VALUE_NONE, 'Do not wait for jobs to stop.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		parent::execute($input, $output);
		$filters = $input->getArgument('filter');
		if(!$filters){
			$config = $this->getContainer()->getParameter('upstart');
			$this->exec('initctl emit %s', [$config['project'].'.stop']);
			return true;
		}
		$jobs = $this->filter($filters);
		$output->writeln('Not implemented yet!');
	}

}
