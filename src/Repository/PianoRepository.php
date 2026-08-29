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
    public function filtra(?string $q, ?int $filaId): array
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.area', 'a')
            ->join('a.fila', 'f')
            ->orderBy('f.codice', 'ASC')
            ->addOrderBy('a.lato', 'ASC')
            ->addOrderBy('p.numero', 'ASC');

        if (null !== $q && '' !== trim($q)) {
            $qb->andWhere('p.etichetta LIKE :q OR f.codice LIKE :q OR f.nome LIKE :q')
                ->setParameter('q', '%'.trim($q).'%');
        }

        if (null !== $filaId) {
            $qb->andWhere('f.id = :filaId')->setParameter('filaId', $filaId);
        }

        return $qb->getQuery()->getResult();
    }
}
