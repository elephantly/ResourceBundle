<?php

namespace Elephantly\ResourceBundle\Doctrine\ORM;

interface GenericRepositoryInterface
{

    public function find( $id );

    public function findOneBy( array $criteria );

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    public function filter(QueryBuilder $queryBuilder, array $criteria = array());

    public function sort(QueryBuilder $queryBuilder, array $sorting = array());

    public function save($entity);

    public function delete($entity);

}
