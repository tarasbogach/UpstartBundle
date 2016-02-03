<?php

namespace SfNix\UpstartBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpstartInstallCommand extends Base{

	protected function configure(){
		parent::configure();
		$this
			->setName('upstart:install')
			->addOption(
				'no-bash-completion',
				'nbc',
				InputOption::VALUE_NONE,
				'Do not try to install or update bash completion script for bin/upstart application.'
			)
			->setDescription(<<<DESC
Generate and install upstart files derived from you configuration.
Use job names and tags as filter. Apply to all jobs if no filters are specified.
It also will try to enable bash completion for  bin/upstart application, including possible filter arguments.
DESC
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output){
		parent::execute($input, $output);
		$container = $this->getContainer();
		$config = $container->getParameter('upstart');

//		$rootDir = $container->getParameter('kernel.root_dir');
//		$completionFile = "$rootDir/bin_upstart_bash_completion.sh";
//		echo $rootDir;
//		exit();
//		if(file_exists()){
//
//		}

		$filters = $input->getArgument('filter');
		if($filters){
			$jobs = $this->filter($filters);
		}else{
			$jobs = $config['job'];
			$deleteCommand = $this->getApplication()->has('upstart:delete') ?
				$this->getApplication()->get('upstart:delete') :
				$this->getApplication()->get('delete');
			$deleteCommand->run(new ArrayInput([]), $output);
		}
		$dir = "{$config['configDir']}/{$config['project']}";
		if(!file_exists($dir)){
			if(mkdir($dir)){
				$output->writeln("<info>Dir '$dir' is created.</info>");
			}else{
				$output->writeln("<error>Can not mkdir '$dir'.</error>");
				throw new \Exception("Can not mkdir '$dir'.");
			}
		}
		$stanzaNameMap = [
			'preStart' => 'pre-start',
			'postStart' => 'post-start',
			'preStop' => 'pre-stop',
			'postStop' => 'post-stop',
			'startOn' => 'start on',
			'stopOn' => 'stop on',
			'normalExit' => 'normal exit',
			'respawnLimit' => 'respawn limit',
			'apparmorLoad' => 'apparmor load',
			'apparmorSwitch' => 'apparmor switch',
			'killSignal' => 'kill signal',
			'killTimeout' => 'kill timeout',
			'reloadSignal' => 'reload signal',
		];
		foreach($jobs as $options){
			$controllerContent = [];
			$instanceContent = [];
			if($options['quantity'] > 1){
				$instanceContent[] = 'instance $instance';
			}
			foreach($options['native'] as $stanza => $value){
				if(isset($stanzaNameMap[$stanza])){
					$stanza = $stanzaNameMap[$stanza];
				}
				switch($stanza){
					case 'start on':
					case 'stop on':
						if($options['quantity'] > 1){
							$controllerContent[] = "$stanza $value";
						}else{
							$instanceContent[] = "$stanza $value";
						}
						break;
					case 'respawn':
					case 'manual':
					case 'task':
						if($value){
							$instanceContent[] = $stanza;
						}
						break;
					case 'script':
						$value = strtr($value, ["\n" => "\n\t"]);
						$instanceContent[] = "script\n\t$value\nend script";
						break;
					case 'emits':
					case 'export':
						foreach($value as $item){
							$instanceContent[] = "$stanza $item";
						}
						break;
					case 'env':
						foreach($value as $itemName => $item){
							if($item){
								$instanceContent[] = "$stanza $itemName=$item";
							}else{
								$instanceContent[] = "$stanza $itemName";
							}
						}
						break;
					case 'respawn limit':
					case 'normal exit':
						$instanceContent[] = "$stanza " . implode(' ', $value);
						break;
					case 'cgroup':
					case 'limit':
						foreach($value as $item){
							$instanceContent[] = "$stanza " . implode(' ', $item);
						}
						break;
					default:
						$instanceContent[] = "$stanza $value";
						break;
				}
			}
			if($options['quantity'] > 1){
				$instances = implode(' ', range(1, $options['quantity'], 1));
				$controllerContent = implode("\n", $controllerContent);
				$controllerContent = <<<BODY
$controllerContent
pre-start script
    for instance in $instances
    do
        start {$config['project']}/{$options['name']}.instance instance=\$instance || :
    done
end script

post-stop script
    for instance in $instances
    do
        stop {$config['project']}/{$options['name']}.instance instance=\$instance || :
    done
end script

BODY;
				$controllerFile = "$dir/{$options['name']}.conf";
				if(file_put_contents($controllerFile, $controllerContent)){
					$output->writeln("<info>Created '$controllerFile'.</info>");
				}else{
					throw new \Exception("Can not write '$controllerFile'.");
				}
				$instanceFile = "$dir/{$options['name']}.instance.conf";
			}else{
				$instanceFile = "$dir/{$options['name']}.conf";
			}
			$instanceContent = implode("\n", $instanceContent) . "\n";
			if(file_put_contents($instanceFile, $instanceContent)){
				$output->writeln("<info>Created '$instanceFile'.</info>");
			}else{
				throw new \Exception("Can not write '$instanceFile'.");
			}
		}
	}

}
