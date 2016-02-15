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
			->addOption('tail', 't', InputOption::VALUE_OPTIONAL, 'Size of a tail (not working with multiple logs).', 20)
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		parent::execute($input, $output);
		$config = $this->getContainer()->getParameter('upstart');
		$filters = $input->getArgument('filter');
		if($filters){
			$jobs = $this->filter($filters);
		}else{
			$jobs = $config['job'];
		}
		$args = [];
		$hasExpands = false;
		foreach($jobs as $job){
			if($job['quantity']>1){
				$hasExpands = true;
				$log = explode('.', $job['log']);
				$ext = array_pop($log);
				$log = implode('.', $log);
				$args[] = escapeshellarg($log).'*.'.escapeshellarg($ext);
			}else{
				$args[] = escapeshellarg($job['log']);
			}
		}
		if(count($args) == 1 && !$hasExpands){
			array_unshift($args, '-n '.escapeshellarg($input->getOption('tail')));
		}
		if($input->getOption('watch')){
			$this->passthru("tail -F ".implode(' ', $args), []);
		}else{
			$return = $this->exec("tail ".implode(' ', $args), []);
			$output->writeln($return);
		}
	}

}
