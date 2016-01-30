<?php

namespace SfNix\UpstartBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;
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
		$config = $this->getContainer()->getParameter('upstart');
		$filters = $input->getArgument('filter');
		if($filters){
			$jobs = $this->filter($filters);
		}else{
			$jobs = $config['job'];
			$this->getApplication()->find('upstart:delete')->run(new ArrayInput([]), $output);
		}
		$dir = $file = "{$config['configDir']}/{$config['project']}";
		if(!file_exists($dir)){
			if(mkdir($dir)){
				$output->writeln("<info>Dir '$dir' is created.</info>");
			}else{
				$output->writeln("<error>Can not mkdir '$dir'.</error>");
				return;
			}
		}
		$stanzaNameMap = [
			'preStart'=>'pre-start',
			'postStart'=>'post-start',
			'preStop'=>'pre-stop',
			'postStop'=>'post-stop',
			'startOn'=>'start on',
			'stopOn'=>'stop on',
			'normalExit'=>'normal exit',
			'respawnLimit'=>'respawn limit',
			'apparmorLoad'=>'apparmor load',
			'apparmorSwitch'=>'apparmor switch',
			'killSignal'=>'kill signal',
			'killTimeout'=>'kill timeout',
			'reloadSignal'=>'reload signal',
		];
		foreach($jobs as $options){
			$content = [];
			foreach($options['native'] as $stanza=>$value){
				if(isset($stanzaNameMap[$stanza])){
					$stanza = $stanzaNameMap[$stanza];
				}
				switch($stanza){
					case 'script':
						$value = strtr($value, ["\n" => "\n\t"]);
						$content[] = "script\n\t$value\nend script";
						break;
					case 'emits':
					case 'export':
						foreach($value as $item){
							$content[] = "$stanza $item";
						}
						break;
					case 'env':
						foreach($value as $itemName=>$item){
							if($item){
								$content[] = "$stanza $itemName=$item";
							}else{
								$content[] = "$stanza $itemName";
							}
						}
						break;
					case 'respawn limit':
					case 'normal exit':
						$content[] = "$stanza ".implode(' ', $value);
						break;
					case 'cgroup':
					case 'limit':
						foreach($value as $item){
							$content[] = "$stanza ".implode(' ', $item);
						}
						break;
					default:
						$content[] = "$stanza $value";
						break;
				}
			}
			$content = implode("\n", $content)."\n";
			$file = "$dir/{$options['name']}.conf";
			if(file_put_contents($file, $content)){
				$output->writeln("<info>Created '$file'.</info>");
			}else{
				$output->writeln("<error>Can not write '$file'.</error>");
				return;
			}
			#TODO add quantity support.
		}
	}

}
