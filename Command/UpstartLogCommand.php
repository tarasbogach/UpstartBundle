<?php

namespace SfNix\UpstartBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpstartLogCommand extends Base{

	protected function configure(){
		parent::configure();
		$this
			->setName('upstart:log')
			->setDescription('Show tails of logs of jobs. It can show logs continuously with --watch (-w) option. If you are redirecting script output in any way then you need to use log, logDir job config options. Use job names and tags as filter. Apply to all jobs if no filters are specified.')
			->addOption('watch', 'w', InputOption::VALUE_NONE, 'Show statuses continuously.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		parent::execute($input, $output);
		$filters = $input->getArgument('filter');
		if(!$filters){
//			$config = $this->getContainer()->getParameter('upstart');
//			if($input->getOption('watch')){
//				$interval = $input->getOption('interval');
//				$this->exec('watch -n %s %s', [$interval, "initctl list | grep {$config['project']}/"]);
//			}else{
//				$this->exec('initctl list | grep %s', [$config['project'].'/']);
//			}
//			return true;
		}
		$jobs = $this->filter($filters);
		$output->writeln('Not implemented yet!');
	}

}
