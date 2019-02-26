<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
{

    public function findAllOrderedByName()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT p FROM AppBundle:Product p ORDER BY p.name ASC')
            ->getResult();
    }

    public function findAllColors()
    {  
        $dql = "SELECT DISTINCT p.color from AppBundle\Entity\Product p";
        $query = $this->getEntityManager()->createQuery($dql);

        return $query->getResult();
    }

    public function findAllOrderedBy($queryBuilder, $column, $direction)
    {
        return $queryBuilder
            ->orderBy('p.'.$column.' '. $direction);
    }

    public function findAllByCategory($queryBuilder, $category)
    {
        return $queryBuilder
            ->andWhere('p.category = :category')
            ->setParameter('category', $category
        );
    }

    public function findAllByColor($queryBuilder, $color)
    {
        return $queryBuilder
            ->andWhere('p.color = :color')
            ->setParameter('color',$color);
    }
}
