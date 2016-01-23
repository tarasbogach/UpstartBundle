<?php

namespace SfNix\UpstartBundle\Command;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
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

	/**
	 * @var Logger
	 */
	protected $logger;

	protected function execute(InputInterface $input, OutputInterface $output){
		$this->input = $input;
		$this->output = $output;
		$this->logger = $this->getContainer()->get('logger');
	}

	protected function passthru($commandTmpl, $arguments = []){
		$arguments = array_map('escapeshellarg', $arguments);
		$command = vsprintf($commandTmpl, $arguments);
		$this->logger->info(__FUNCTION__, [$command]);
		passthru($command);
	}

	protected function exec($commandTmpl, $arguments = []){
		$arguments = array_map('escapeshellarg', $arguments);
		$command = vsprintf($commandTmpl, $arguments);
		$this->logger->info(__FUNCTION__, [$command]);
		return shell_exec($command);
	}

	protected function filter($filters){
		$config = $this->getContainer()->getParameter('upstart');
		$jobs = [];
		$jobsNames = [];
		foreach($config['job'] as $job){
			if(in_array($job['name'], $filters) || array_intersect($job['tag'], $filters)){
				$jobs[] = $job;
				$jobsNames[] = $job['name'];
			}
		}
		$this->logger->info(__FUNCTION__, ['job'=>$jobsNames]);
		return $jobs;
	}
}