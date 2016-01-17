<?php

namespace SfNix\UpstartBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\UnsetKeyException;

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
        $rootChildren = $rootNode->children();
        $defaultChildren = $rootChildren->arrayNode('default')->children();
        $this->appendJobTo($defaultChildren);
        $defaultChildren->end()->end();
        $jobPrototypeChildren = $rootChildren->arrayNode('job')->prototype('array')->children();
        $this->appendJobTo($jobPrototypeChildren);
        $jobPrototypeChildren->end()->end()->end();
        $rootChildren->end();
        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $node
     * @return NodeBuilder
     */
    protected function appendJobTo(NodeBuilder $node){
        return $node
            #Process Definition
            ->scalarNode('exec')->end()
            ->scalarNode('preStart')->end()
            ->scalarNode('postStart')->end()
            ->scalarNode('preStop')->end()
            ->scalarNode('postStop')->end()
            ->scalarNode('script')->end()

            #Event Definition
            ->booleanNode('manual')->defaultFalse()->end()
            ->scalarNode('startOn')->end()
            ->scalarNode('stopOn')->end()

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
            ->scalarNode('respawn')
                ->validate()->ifNull()->thenUnset()->end()
            ->end()
            ->arrayNode('respawnLimit')
                ->prototype('integer')->min(1)->defaultValue(1)->end()
            ->end()
            ->scalarNode('task')
                ->validate()->ifNull()->thenUnset()->end()
            ->end()

            #Instances
            ->scalarNode('instance')
                ->validate()->ifNull()->thenUnset()->end()
            ->end()

            #Documentation
            ->scalarNode('author')->end()
            ->scalarNode('description')->end()
            ->arrayNode('emits')
                ->prototype('scalar')->end()
            ->end()
            ->scalarNode('version')->end()
            ->scalarNode('usage')->end()

            #Process environment
            ->scalarNode('apparmorLoad')->end()
            ->scalarNode('apparmorSwitch')->end()
            ->arrayNode('cgroup')
                ->example([
                    ['CONTROLLER','NAME','KEY VALUE'],
                    ['CONTROLLER','NAME','KEY VALUE'],
                ])
                ->prototype('array')
                    ->children()
                        ->scalarNode(0)->info('CONTROLLER')->end()
                        ->scalarNode(1)->info('NAME')->end()
                        ->scalarNode(2)->info('KEY VALUE')->end()
                    ->end()
                ->end()
            ->end()
            ->enumNode('console')
                ->defaultValue('logged')
                ->values(['logged', 'output', 'owner', 'none'])
            ->end()
            ->scalarNode('chdir')->end()
            ->scalarNode('chroot')->end()
            ->arrayNode('limit')
                ->prototype('array')
                    ->children()
                        ->enumNode(0)->values([
                            'core', 'cpu', 'data', 'fsize', 'memlock', 'msgqueue',
                            'nice', 'nofile', 'nproc', 'rss', 'rtprio', 'sigpending',
                            'stack'
                        ])->end()
                        ->scalarNode(1)
                            ->defaultValue('unlimited')
                            ->validate()
                            ->always()
                            ->then(function ($v) {
                                if(is_int($v) || $v=='unlimited'){
                                    return $v;
                                }else{
                                    throw new \InvalidArgumentException(sprintf(
                                        'Value can be int or "unlimited", but %s was passed.',
                                        json_encode($v)
                                    ));
                                }
                            })
                            ->end()
                        ->end()
                        ->scalarNode(2)
                            ->defaultValue('unlimited')
                            ->validate()
                            ->always()
                            ->then(function ($v) {
                                if(is_int($v) || $v=='unlimited'){
                                    return $v;
                                }else{
                                    throw new \InvalidArgumentException(sprintf(
                                        'Value can be int or "unlimited", but %s was passed.',
                                        json_encode($v)
                                    ));
                                }
                            })
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->scalarNode('nice')
                ->validate()
                ->always()
                ->then(function ($v) {
                    if(is_null($v)){
                        throw new UnsetKeyException('Unsetting key');
                    }elseif(is_int($v) && -20 <= $v && $v <= 19){
                        return $v;
                    }else{
                        throw new \InvalidArgumentException(sprintf(
                            'Value can be -20 <= int <= 19.',
                            json_encode($v)
                        ));
                    }
                })
                ->end()
            ->end()
            ->scalarNode('oomScore')
                ->validate()
                ->always()
                ->then(function ($v) {
                    if(is_null($v)){
                        throw new UnsetKeyException('Unsetting key');
                    }elseif(is_int($v) && -999 <= $v && $v <= 1000){
                        return $v;
                    }elseif($v=='never'){
                        return $v;
                    }else{
                        throw new \InvalidArgumentException(sprintf(
                            'Value can be -999 <= int <= 1000 or "never", but %s was passed.',
                            json_encode($v)
                        ));
                    }
                })
                ->end()
            ->end()
            ->scalarNode('setgid')->end()
            ->scalarNode('setuid')->end()
            ->scalarNode('umask')->end()

            #Process Control
            ->enumNode('expect')
                ->values(['fork', 'deamon', 'stop', null])
                ->validate()->ifNull()->thenUnset()->end()
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
                    ->scalarNode('project')->isRequired()->cannotBeEmpty()->end()
                    ->integerNode('quantity')->defaultValue(1)->end()
                    ->arrayNode('tag')
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('name')->end()
                    ->scalarNode('command')->end()
                    ->integerNode('verbose')->min(0)->max(3)->defaultValue(0)->end()
                    ->scalarNode('env')->defaultValue('dev')->end()
                ->end()
            ->end();
    }
}
