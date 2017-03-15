<?php

namespace Elephantly\OGBundle\Doctrine\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory;
use Elephantly\ResourceBundle\Doctrine\ORM\GenericRepositoryInterface;
use Elephantly\ResourceBundle\Doctrine\ORM\GenericRepository;

class RepositoryFactory implements RepositoryFactory
{

    private $repositoryList = array();

    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $entityName = ltrim($entityName, '\\');

        if (isset($this->repositoryList[$entityName])) {
            return $this->repositoryList[$entityName];
        }

        $repository = $this->createRepository($entityManager, $entityName);

        $this->repositoryList[$entityName] = $repository;

        return $repository;
    }

    protected function createRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $metadata = $entityManager->getClassMetadata($entityName);

        $repository = new GenericRepository($entityManager, $metadata);

        return $repository;
    }
}
