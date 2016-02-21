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

	protected function checkFilters($filters){
		$config = $this->getContainer()->getParameter('upstart');
		$possibleFilters = array_merge($config['jobNames'], $config['tagNames']);
		$wrongFilters = array_diff($filters, $possibleFilters);
		if($wrongFilters){
			$closestFilters = [];
			if(function_exists('levenshtein')){
				foreach($wrongFilters as $filter){
					usort(
						$possibleFilters,
						function ($a, $b) use ($filter){
							return levenshtein($a, $filter) - levenshtein($b, $filter);
						}
					);
					$closestFilters[] = "Did you mean '{$possibleFilters[0]}' instead of '$filter'?";
				}
			}
			$desc = [];
			$desc[] = 'Unknown filters found: ' . implode(', ', $wrongFilters) . '.';
			$desc[] = 'Filter can be job or tag name only.';
			if($closestFilters){
				$desc[] = implode("\n", $closestFilters);
			}
			throw new \Exception(implode("\n", $desc));
		}
	}

	protected function filter($filters){
		$config = $this->getContainer()->getParameter('upstart');
		$this->checkFilters($filters);
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