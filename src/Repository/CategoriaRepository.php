<?php

namespace App\Repository;

use App\Entity\Categoria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categoria>
 */
class CategoriaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categoria::class);
    }

    /**
     * @return Categoria[]
     */
    public function filtra(?string $q): array
    {
        $qb = $this->createQueryBuilder('c')->orderBy('c.nome', 'ASC');

        if (null !== $q && '' !== trim($q)) {
            $qb->andWhere('c.nome LIKE :q')->setParameter('q', '%'.trim($q).'%');
        }

        return $qb->getQuery()->getResult();
    }
}
