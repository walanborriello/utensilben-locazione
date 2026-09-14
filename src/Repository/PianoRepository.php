<?php

namespace App\Repository;

use App\Entity\Piano;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Piano>
 */
class PianoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Piano::class);
    }

    /**
     * @return Piano[]
     */
    public function filtra(?string $q): array
    {
        $qb = $this->createQueryBuilder('p')->orderBy('p.nome', 'ASC');

        if (null !== $q && '' !== trim($q)) {
            $qb->andWhere('p.nome LIKE :q')->setParameter('q', '%'.trim($q).'%');
        }

        return $qb->getQuery()->getResult();
    }
}
