<?php

namespace Elephantly\ResourceBundle\Doctrine\ORM;

use Doctrine\ORM\EntityRepository as BaseEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;

class GenericRepository extends BaseEntityRepository implements GenericRepositoryInterface
{

    public function find($id)
    {
        $queryBuilder = $this->createQueryBuilder('e');

        $queryBuilder
            ->where($queryBuilder->expr()->eq('e.id', ':id'))
            ->setParameter('id', $id);

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneBy(array $criteria)
    {
        $queryBuilder = $this->createQueryBuilder('e');

        if (!empty($criteria))
        {
            $this->filter($queryBuilder, $criteria);
        }

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $queryBuilder = $this->createQueryBuilder('e');

        if (!empty($criteria))
        {
            $this->filter($queryBuilder, $criteria);
        }

        if(!is_null($orderBy))
        {
            $this->sort($queryBuilder, $orderBy);
        }

        if(!is_null($limit))
        {
             $queryBuilder->setMaxResults($limit);
        }

        if(!is_null($offset))
        {
            $queryBuilder->setFirstResult($offset);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }


    public function filter(QueryBuilder $queryBuilder, array $criteria = array())
    {
        foreach ($criteria as $property => $value) {
            $name = 'e.'.$property;
            if (null === $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->isNull($name));
            } elseif (is_array($value)) {
                $queryBuilder->andWhere($queryBuilder->expr()->in($name, $value));
            } elseif ('' !== $value) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->eq($name, ':'.$property))
                    ->setParameter($property, $value);
            }
        }
    }

    public function sort(QueryBuilder $queryBuilder, array $sorting = array())
    {
        foreach ($sorting as $property => $order) {
            if (!empty($order)) {
                $queryBuilder->addOrderBy('e.'.$property, $order);
            }
        }
    }

    public function save($entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function delete($entity)
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }

}
