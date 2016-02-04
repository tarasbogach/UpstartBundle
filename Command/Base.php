<?php

namespace SfNix\UpstartBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Base extends ContainerAwareCommand{

	protected function configure(){
		$this->addArgument('filter', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Job names or tags.');
	}

	/**
	 * @var InputInterface
	 */
	protected $input;

	/**
	 * @var OutputInterface
	 */
	protected $output;

	protected function execute(InputInterface $input, OutputInterface $output){
		$this->input = $input;
		$this->output = $output;
	}

	protected function passthru($commandTmpl, $arguments = []){
		$arguments = array_map('escapeshellarg', $arguments);
		$command = vsprintf($commandTmpl, $arguments);
		passthru($command);
	}

	protected function exec($commandTmpl, $arguments = []){
		$arguments = array_map('escapeshellarg', $arguments);
		$command = vsprintf($commandTmpl, $arguments);
		$this->output->writeln("<info># $command</info>");
		return shell_exec($command);
	}

	protected function filter($filters){
		$config = $this->getContainer()->getParameter('upstart');
		$wrongFilters = array_diff($filters, $config['jobNames'], $config['tagNames']);
		if($wrongFilters){
			throw new \Exception('Unknown filters found: '.implode(', ', $wrongFilters));
		}
		$jobs = [];
		$jobsNames = [];
		foreach($config['job'] as $job){
			if(in_array($job['name'], $filters) || (isset($job['tag']) && array_intersect($job['tag'], $filters))){
				$jobs[] = $job;
				$jobsNames[] = $job['name'];
			}
		}
		$jobsNames = implode(', ', $jobsNames);
		$this->output->writeln("<info>Filtered jobs are: $jobsNames.</info>");
		return $jobs;
	}
}