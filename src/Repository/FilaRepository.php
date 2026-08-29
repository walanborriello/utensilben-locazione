<?php

namespace App\Repository;

use App\Entity\Fila;
use App\Enum\TipoFila;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fila>
 */
class FilaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fila::class);
    }

    /**
     * @return Fila[]
     */
    public function filtra(?string $q, ?TipoFila $tipo): array
    {
        $qb = $this->createQueryBuilder('f')->orderBy('f.codice', 'ASC');

        if (null !== $q && '' !== trim($q)) {
            $qb->andWhere('f.codice LIKE :q OR f.nome LIKE :q')->setParameter('q', '%'.trim($q).'%');
        }

        if (null !== $tipo) {
            $qb->andWhere('f.tipo = :tipo')->setParameter('tipo', $tipo);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Numero di materiali (distinti) posizionati in ciascuna fila, per il
     * report grafico della dashboard. Solo le file con almeno un materiale
     * posizionato compaiono nel risultato.
     *
     * @return array<int, array{fila: Fila, totale: int}>
     */
    public function materialiPerFila(): array
    {
        $risultati = $this->createQueryBuilder('f')
            ->select('f AS fila', 'COUNT(DISTINCT pos.materiale) AS totale')
            ->join('f.aree', 'a')
            ->join('a.piani', 'p')
            ->join('p.posizionamenti', 'pos')
            ->groupBy('f.id')
            ->orderBy('totale', 'DESC')
            ->addOrderBy('f.codice', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (array $riga): array => ['fila' => $riga['fila'], 'totale' => (int) $riga['totale']],
            $risultati
        );
    }
}
