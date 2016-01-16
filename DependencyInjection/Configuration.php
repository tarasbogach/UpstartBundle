<?php

namespace SfNix\UpstartBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('upstart');

        $rootNode
            ->children()
                ->arrayNode('default')
                    ->children()
                        #Process Definition
                        ->scalarNode('exec')->defaultNull()->end()
                        ->scalarNode('preStart')->defaultNull()->end()
                        ->scalarNode('postStart')->defaultNull()->end()
                        ->scalarNode('preStop')->defaultNull()->end()
                        ->scalarNode('postStop')->defaultNull()->end()
                        ->scalarNode('script')->defaultNull()->end()
                        #Event Definition
                        ->booleanNode('manual')->defaultFalse()->end()
                        ->scalarNode('startOn')->defaultNull()->end()
                        ->scalarNode('stopOn')->defaultNull()->end()
                        #Job Environment
                        ->arrayNode('env')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('export')
                            ->prototype('scalar')->end()
                        ->end()
                        #Services, tasks and respawning
                        ->arrayNode('normalExit')
                         ->prototype('scalar')->end()
                        ->end()
                        ->booleanNode('respawn')->defaultFalse()->end()
                        ->arrayNode('respawnLimit')
                            ->prototype('integer')->min(1)->defaultValue(1)->end()
                        ->end()
                        ->booleanNode('task')->defaultFalse()->end()
                        #Instances
                        ->booleanNode('instance')->defaultFalse()->end()
                        #Documentation
                        ->scalarNode('author')->defaultNull()->end()
                        ->scalarNode('description')->defaultNull()->end()
                        ->arrayNode('emits')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('version')->defaultNull()->end()
                        ->scalarNode('usage')->defaultNull()->end()
                        #Process environment
                        //TODO apparmorLoad
                        //TODO apparmorSwitch
                        //TODO cgroup
                        ->enumNode('console')
                            ->values(['logged', 'output', 'owner', 'none'])
                            ->defaultValue('logged')
                        ->end()
                        ->scalarNode('chdir')->defaultNull()->end()
                        ->scalarNode('chroot')->defaultNull()->end()
                        ->arrayNode('limit')
                            ->prototype('array')
                                ->children()
                                    ->enumNode(0)->values(['logged', 'output', 'owner', 'none'])->end()
                                    ->integerNode(1)->end()//TODO unlimited
                                    ->integerNode(2)->end()//TODO unlimited
                                ->end()
                            ->end()
                        ->end()
                        ->integerNode('nice')->defaultNull()->min(-20)->max(19)->end()
                        ->integerNode('oomScore')->defaultNull()->min(-999)->max(1000)->end()//TODO never
                        ->scalarNode('setgid')->defaultNull()->end()
                        ->scalarNode('setuid')->defaultNull()->end()
                        ->scalarNode('umask')->defaultNull()->end()

                        #Process Control
                        ->enumNode('expect')
                            ->values(['fork', 'deamon', 'stop'])
                            ->defaultNull()
                        ->end()
                        ->enumNode('killSignal')
                            ->values([
                                'SIGHUP','SIGINT','SIGQUIT','SIGILL','SIGTRAP',
                                'SIGIOT','SIGBUS','SIGFPE','SIGKILL','SIGUSR1',
                                'SIGSEGV','SIGUSR2','SIGPIPE','SIGALRM','SIGTERM',
                                'SIGSTKFLT','SIGCHLD','SIGCONT','SIGSTOP','SIGTSTP',
                                'SIGTTIN','SIGTTOU','SIGURG','SIGXCPU','SIGXFSZ',
                                'SIGVTALRM','SIGPROF','SIGWINCH','SIGIO','SIGPWR',
                            ])
                            ->defaultValue('SIGTERM')
                        ->end()
                        ->integerNode('killTimeout')->min(1)->defaultValue(1)->end()
                        ->enumNode('reloadSignal')
                            ->values([
                                'SIGHUP','SIGINT','SIGQUIT','SIGILL','SIGTRAP',
                                'SIGIOT','SIGBUS','SIGFPE','SIGKILL','SIGUSR1',
                                'SIGSEGV','SIGUSR2','SIGPIPE','SIGALRM','SIGTERM',
                                'SIGSTKFLT','SIGCHLD','SIGCONT','SIGSTOP','SIGTSTP',
                                'SIGTTIN','SIGTTOU','SIGURG','SIGXCPU','SIGXFSZ',
                                'SIGVTALRM','SIGPROF','SIGWINCH','SIGIO','SIGPWR',
                            ])
                            ->defaultValue('SIGTERM')
                        ->end()
                        #Symfony
                        ->arrayNode('sf')
                            ->children()
                                ->scalarNode('command')->defaultNull()->end()
                                ->integerNode('verbose')->min(0)->max(3)->defaultValue(0)->end()
                                ->scalarNode('env')->defaultValue('dev')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('job')
                ->end()
            ->end()
         ->end()
        ;

        return $treeBuilder;
    }
}
