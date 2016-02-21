<?php

namespace SfNix\UpstartBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpstartRestartCommand extends Base{

	protected function configure(){
		parent::configure();
		$this
			->setName('upstart:restart')
			->setDescription('Stop and start jobs. Use job names and tags as filter. Apply to all jobs if no filters are specified.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		parent::execute($input, $output);
		$config = $this->getContainer()->getParameter('upstart');
		$filters = $input->getArgument('filter');
		if($filters){
			$this->checkFilters($filters);
			foreach($filters as $filter){
				if(in_array($filter, $config['tagNames'])){
					$this->exec('initctl emit %s', ["{$config['project']}.{$filter}.stop"]);
					$this->exec('initctl emit %s', ["{$config['project']}.{$filter}.start"]);
				}elseif(in_array($filter, $config['jobNames'])){
					$this->exec('initctl stop %s', ["{$config['project']}/{$filter}"]);
					$this->exec('initctl start %s', ["{$config['project']}/{$filter}"]);
				}
			}
		}else{
			$config = $this->getContainer()->getParameter('upstart');
			$this->exec('initctl emit %s', ["{$config['project']}.stop"]);
			$this->exec('initctl emit %s', ["{$config['project']}.start"]);
		}
	}

}
