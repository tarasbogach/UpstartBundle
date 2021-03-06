<?php

namespace SfNix\UpstartBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpstartDeleteCommand extends Base{

	protected function configure(){
		parent::configure();
		$this
			->setName('upstart:delete')
			->setDescription('Delete upstart files and bash completion scripts generated by this bundle. Use job names and tags as filter. Apply to all jobs if no filters are specified.');
	}

	protected function delete($file){
		if(unlink($file)){
			$this->output->writeln("<info>Deleted '$file'.</info>");
			return true;
		}else{
			$this->output->writeln("<error>Can not delete '$file'.</error>");
			return false;
		}
	}
	protected function execute(InputInterface $input, OutputInterface $output){
		parent::execute($input, $output);
		$filters = $input->getArgument('filter');
		$config = $this->getContainer()->getParameter('upstart');
		$dir = "{$config['configDir']}/{$config['project']}";
		$deletions = [false => 0, true => 0];
		if($filters){
			$jobs = $this->filter($filters);
			foreach($jobs as $job){
				$file = "$dir/{$job['name']}.conf";
				if(file_exists($file)){
					$deletions[$this->delete($file)]++;
				}
				$files = glob("$dir/{$job['name']}-*.conf");
				foreach($files as $file){
					$deletions[$this->delete($file)]++;
				}
			}
		}else{
			$files = glob("$dir/*.conf");
			foreach($files as $file){
				$deletions[$this->delete($file)]++;
			}
		}
		$tag = $deletions[false] == 0 ? 'info' : 'errors';
		$output->writeln("<$tag>Done with {$deletions[true]} deletions, {$deletions[false]} errors.</$tag>");
	}

}
