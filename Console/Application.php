<?php

namespace SfNix\UpstartBundle\Console;

use SfNix\UpstartBundle\Command\UpstartDeleteCommand;
use SfNix\UpstartBundle\Command\UpstartInstallCommand;
use SfNix\UpstartBundle\Command\UpstartListCommand;
use SfNix\UpstartBundle\Command\UpstartLogCommand;
use SfNix\UpstartBundle\Command\UpstartRestartCommand;
use SfNix\UpstartBundle\Command\UpstartStartCommand;
use SfNix\UpstartBundle\Command\UpstartStopCommand;
use SfNix\UpstartBundle\Command\UpstartTestCommand;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Kernel;

class Application extends BaseApplication{

	private $kernel;
	private $commandsRegistered = false;

	/**
	 * Constructor.
	 *
	 * @param KernelInterface $kernel A KernelInterface instance
	 */
	public function __construct(KernelInterface $kernel){
		$this->kernel = $kernel;

		parent::__construct(
			'Symfony',
			Kernel::VERSION . ' - ' . $kernel->getName() . '/' . $kernel->getEnvironment() .
			($kernel->isDebug() ? '/debug' : '')
		);

		$this->getDefinition()->addOption(
			new InputOption(
				'--env',
				'-e',
				InputOption::VALUE_REQUIRED,
				'The Environment name.',
				$kernel->getEnvironment()
			)
		);
		$this->getDefinition()->addOption(
			new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.')
		);
	}

	/**
	 * Gets the Kernel associated with this Console.
	 *
	 * @return KernelInterface A KernelInterface instance
	 */
	public function getKernel(){
		return $this->kernel;
	}

	/**
	 * Runs the current application.
	 *
	 * @param InputInterface $input An Input instance
	 * @param OutputInterface $output An Output instance
	 *
	 * @return int 0 if everything went fine, or an error code
	 */
	public function doRun(InputInterface $input, OutputInterface $output){
		$this->kernel->boot();

		if(!$this->commandsRegistered){
			$this->registerCommands();

			$this->commandsRegistered = true;
		}

		$container = $this->kernel->getContainer();

		foreach($this->all() as $command){
			if($command instanceof ContainerAwareInterface){
				$command->setContainer($container);
			}
		}

		$this->setDispatcher($container->get('event_dispatcher'));

		return parent::doRun($input, $output);
	}

	/**
	 * Gets the default commands that should always be available.
	 *
	 * @return Command[] An array of default Command instances
	 */
	protected function getDefaultCommands(){
		$list = new ListCommand();
		$list->setName('helpList');
		$this->setDefaultCommand('helpList');
		return [new HelpCommand(), $list];
	}

	protected function registerCommands(){
		$this->add((new UpstartInstallCommand())->setName('install'));
		$this->add((new UpstartStartCommand())->setName('start'));
		$this->add((new UpstartStopCommand())->setName('stop'));
		$this->add((new UpstartRestartCommand())->setName('restart'));
		$this->add((new UpstartListCommand())->setName('list'));
		$this->add((new UpstartLogCommand())->setName('log'));
		$this->add((new UpstartDeleteCommand())->setName('delete'));
		$this->add((new UpstartTestCommand())->setName('test'));
	}
}