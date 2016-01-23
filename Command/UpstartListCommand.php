<?php

namespace SfNix\UpstartBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpstartListCommand extends Base{

	protected function configure(){
		parent::configure();
		$this
			->setName('upstart:list')
			->setDescription('List jobs, and their statuses. It can show statuses continuously with --watch (-w) option. Refresh interval is 1 second by default, see --interval (-i) option. Use job names and tags as filter. Apply to all jobs if no filters are specified.')
			->addOption('watch', 'w', InputOption::VALUE_NONE, 'Show statuses continuously.')
			->addOption('interval', 'i', InputOption::VALUE_OPTIONAL, 'Interval for status refresh.', 1)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		parent::execute($input, $output);
		$filters = $input->getArgument('filter');
		if(!$filters){
			$config = $this->getContainer()->getParameter('upstart');
			if($input->getOption('watch')){
				$interval = $input->getOption('interval');
				$this->exec('watch -n %s %s', [$interval, "initctl list | grep {$config['project']}/"]);
			}else{
				$this->exec('initctl list | grep %s', [$config['project'].'/']);
			}
			return true;
		}
		$jobs = $this->filter($filters);
		$output->writeln('Not implemented yet!');
	}

}