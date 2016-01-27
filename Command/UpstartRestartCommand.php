<?php

namespace SfNix\UpstartBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpstartRestartCommand extends Base{

	protected function configure(){
		$this
			->setName('upstart:restart')
			->setDescription('Stop and start jobs. Use job names and tags as filter. Apply to all jobs if no filters are specified.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		parent::execute($input, $output);
		$config = $this->getContainer()->get('upstart');
		$filters = $input->getArgument('filter');
		if($filters){
			foreach($filters as $filter){
				if(in_array($filter, $config['tagNames'])){
					$this->exec('initctl emit %s', ["{$config['project']}.{$filter}.stop"]);
					$this->exec('initctl emit %s', ["{$config['project']}.{$filter}.start"]);
				}elseif(in_array($filter, $config['jobNames'])){
					$job = $config['job'][$filter];
					$postfix = $job['quantity'] > 1 ? '-stopper':'';
					$this->exec('initctl start %s', ["{$config['project']}/{$filter}{$postfix}"]);
					$postfix = $job['quantity'] > 1 ? '-starter':'';
					$this->exec('initctl start %s', ["{$config['project']}/{$filter}{$postfix}"]);
				}
			}
		}else{
			$config = $this->getContainer()->getParameter('upstart');
			$this->exec('initctl emit %s', ["{$config['project']}.stop"]);
			$this->exec('initctl emit %s', ["{$config['project']}.start"]);
		}
	}

}
