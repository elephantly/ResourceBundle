<?php

namespace Elephantly\ResourceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('elephantly_resource');

        $this->addSettings($rootNode);

        return $treeBuilder;
    }

    public function addSettings(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
               // we define the array of resources
                ->arrayNode('resources')
                    //we use the attributes as keys
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')
                               ->info('Class of the object you want to serialize')
                               ->isRequired()
                            ->end()
                            ->scalarNode('controller')
                               ->info('Choose your controller, it will need to extend the GenericRestcontroller, class expected')
                               ->defaultNull()
                            ->end()
                            ->scalarNode('entity_manager')
                               ->info('Choose your entity manager, if it is not the default entity manager, service is expected')
                               ->defaultNull()
                            ->end()
                            ->scalarNode('form_type')
                               ->info('Choose your formType, class expected')
                               ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
