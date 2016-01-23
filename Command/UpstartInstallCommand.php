<?php

namespace SfNix\UpstartBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpstartInstallCommand extends Base{

	protected function configure(){
		parent::configure();
		$this
			->setName('upstart:install')
			->setDescription('Generate and install upstart files derived configuration. It also will try to enable bash completion for other commands of this bundle, including arguments derived configuration. Use job names and tags as filter. Apply to all jobs if no filters are specified.');
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		parent::execute($input, $output);
		$filters = $input->getArgument('filter');
		if(!$filters){
//			$config = $this->getContainer()->getParameter('upstart');
//			$this->exec('initctl emit %s', [$config['project'].'-start']);
//			return true;
		}
		$jobs = $this->filter($filters);
		$output->writeln('Not implemented yet!');
	}

}
