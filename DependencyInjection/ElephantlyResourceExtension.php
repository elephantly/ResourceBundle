<?php

namespace Elephantly\ResourceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ElephantlyResourceExtension extends Extension
{
    CONST CONTROLLER_CLASS = 'Elephantly\ResourceBundle\Controller\GenericController';
    CONST REPOSITORY_CLASS = 'Elephantly\ResourceBundle\Doctrine\ORM\GenericRepository';
    CONST ENTITY_MANAGER = 'doctrine.orm.entity_manager';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->handleResources($config, $container);

    }

    public function handleResources(array $config, ContainerBuilder $container)
    {
        foreach ($config['resources'] as $resourceName => $resource) {

            $resourceMetadata = new ClassMetadata($resource['class']);

            $this->registerRepository($container, $resourceName, $resourceMetadata, $resource);
            $this->registerController($container, $resourceName, $resourceMetadata, $resource);

        }

    }

    public function registerRepository(ContainerBuilder $container, $resourceName, ClassMetadata $resourceMetadata, $conf)
    {
        $entityManager = is_null($conf['entity_manager']) ? self::ENTITY_MANAGER : $conf['entity_manager'];

        $container->setParameter(sprintf('elephantly.%s.repository.class', $resourceName), self::REPOSITORY_CLASS);

        $repositoryDefinition = new Definition($container->getParameter(sprintf('elephantly.%s.repository.class', $resourceName)));
        $repositoryDefinition
           ->setFactory(array(
               new Reference($entityManager),
               'getRepository'
           ))
           ->setArguments(array(
               $resourceMetadata->getName()
           ))
       ;

       $container->setDefinition(sprintf('elephantly.%s.repository', $resourceName), $repositoryDefinition);
    }

    public function registerController(ContainerBuilder $container, $resourceName, ClassMetadata $resourceMetadata, $conf)
    {
        $controllerClass = is_null($conf['controller']) ? self::CONTROLLER_CLASS : $conf['controller'];

        $container->setParameter(sprintf('elephantly.%s.controller.class', $resourceName), $controllerClass);

        $controllerDefinition = new Definition($container->getParameter(sprintf('elephantly.%s.controller.class', $resourceName)));
        $controllerDefinition
           ->setArguments(array(
               new Reference(sprintf('elephantly.%s.repository', $resourceName)),
               $resourceMetadata->getName()
           ))
           ->addMethodCall('setContainer', array(new Reference('service_container')))
       ;

       $container->setDefinition(sprintf('elephantly.%s.controller', $resourceName), $controllerDefinition);
   }

}
