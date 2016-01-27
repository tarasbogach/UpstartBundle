<?php

namespace SfNix\UpstartBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class UpstartExtension extends Extension{

	const nameRegExp = '/^[a-z][a-z0-9_.]+[a-z0-9]$/i';

	public function checkName($name, $at){
		if(!preg_match(self::nameRegExp, $name)){
			$error = sprintf("The name '%s' at %s must match '%s'.", $name, $at, self::nameRegExp);
			throw new \InvalidArgumentException($error);
		}
	}
	/**
	 * {@inheritdoc}
	 */
	public function load(array $configs, ContainerBuilder $container){
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);
		$console = $container->getParameter('kernel.root_dir').'/console';
		$jobs = [];
		$tags = [];
		$jobsByName = [];
		foreach($config['job'] as $jobKey => $options){
			if(!isset($config['default']['quantity'])){
				$config['default']['quantity'] = 1;
			}
			foreach($config['default'] as $key0=>$value0){
				if(in_array($key0, ['native'])){
					foreach($value0 as $key1=>$value1){
						if(!isset($options[$key0][$key1])){
							$options[$key0][$key1] = $value1;
						}
					}
				}else{
					if(!isset($options[$key0])){
						$options[$key0] = $value0;
					}
				}
			}

			if(!isset($options['name'])){
				$options['name'] = $jobKey;
			}
			$job = $options['name'];
			$at = sprintf("job with key '%s'", $jobKey);
			$this->checkName($job, $at);
			if(isset($jobs[$job])){
				throw new \InvalidArgumentException(sprintf(
					"The name '%s' at %s is already used for job with key '%s'.",
					$job, $at, $jobs[$job]
				));
			}
			if(isset($tags[$job])){
				throw new \InvalidArgumentException(sprintf(
					"The name '%s' at %s is already used for tag in job '%s'.",
					$job, $at, $tags[$job]
				));
			}
			$jobs[$job] = $jobKey;

			$startOn = [];
			$stopOn = [];
			$startOn[] = "{$config['project']}.start";
			$stopOn[] = "{$config['project']}.stop";
			if(isset($options['tag'])){
				foreach($options['tag'] as $tag){
					$at = sprintf("job '%s' -> tag '%s'", $job, $tag);
					$this->checkName($tag, $at);
					if(isset($jobs[$tag])){
						throw new \InvalidArgumentException(sprintf(
							"The name '%s' at %s is already used for job with key '%s'.",
							$tag, $at, $jobs[$tag]
						));
					}
					$tags[$tag] = $job;
					$startOn[] = "{$config['project']}.{$tag}.start";
					$stopOn[] = "{$config['project']}.{$tag}.stop";
				}
			}
			if(isset($options['native']['startOn'])){
				$startOn[] = $options['native']['startOn'];
			}
			if(isset($options['native']['stopOn'])){
				$stopOn[] = $options['native']['stopOn'];
			}
			$options['native']['startOn'] = '('.implode(') or (', $startOn).')';
			$options['native']['stopOn'] = '('.implode(') or (', $stopOn).')';

			if(!isset($options['native']['exec']) && !isset($options['native']['script'])){
				if(isset($options['command'])){
					$exec = [];
					$exec[] = "php $console {$options['command']}";
					if(isset($options['env'])){$exec[] = "--env ".$options['env'];};
					if(isset($options['debug'])&&!$options['debug']){$exec[] = "--no-debug";};
					if(isset($options['verbose'])){$exec[] = "-".str_repeat('v', $options['verbose']);};
					$options['native']['exec'] = implode(' ', $exec);
				}elseif(isset($options['script'])){
					if(strpos($options['script'], "\n")==false){
						$options['native']['exec'] = $options['script'];
					}else{
						$options['native']['script'] = $options['script'];
					}
				}
			}
			if(isset($options['log'])){
				$options['log'] = "{$options['logDir']}/{$options['log']}";
			}else{
				$options['log'] = "{$options['logDir']}/{$config['project']}_{$options['name']}.log";
			}
			foreach($options as $key=>$value){
				if(!in_array($key, ['native', 'name', 'quantity', 'tag', 'log'])){
					unset($options[$key]);
				}
			}
			$jobsByName[$options['name']]=$options;
		}
		$config['job'] = $jobsByName;
		$config['jobNames'] = array_keys($jobs);
		$config['tagNames'] = array_keys($tags);
		unset($config['default']);
		$container->setParameter('upstart', $config);
		$locator = new FileLocator(__DIR__ . '/../Resources/config/');
		$loader = new Loader\YamlFileLoader($container, $locator);
		$loader->load('config.yml');
	}
}
