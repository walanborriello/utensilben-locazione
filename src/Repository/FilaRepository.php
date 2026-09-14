<?php

namespace App\Repository;

use App\Entity\Fila;
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
    public function filtra(?string $q, ?int $pianoId): array
    {
        $qb = $this->createQueryBuilder('f')
            ->join('f.piano', 'p')->addSelect('p')
            ->orderBy('p.nome', 'ASC')
            ->addOrderBy('f.lettera', 'ASC');

        if (null !== $q && '' !== trim($q)) {
            $qb->andWhere('f.lettera LIKE :q OR p.nome LIKE :q')->setParameter('q', '%'.trim($q).'%');
        }

        if (null !== $pianoId) {
            $qb->andWhere('p.id = :pianoId')->setParameter('pianoId', $pianoId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * File di un piano con le rispettive sezioni già caricate (per la mappa
     * grafica in gestione). Ordinate per lettera DESC: la mappa si legge
     * dal fondo del magazzino (L, muro) verso l'ingresso (A).
     *
     * @return Fila[]
     */
    public function mappaPerPiano(int $pianoId): array
    {
        return $this->createQueryBuilder('f')
            ->addSelect('s')
            ->leftJoin('f.sezioni', 's')
            ->andWhere('f.piano = :pianoId')->setParameter('pianoId', $pianoId)
            ->orderBy('f.lettera', 'DESC')
            ->addOrderBy('s.lettera', 'ASC')
            ->getQuery()
            ->getResult();
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
            ->join('f.sezioni', 's')
            ->join('s.ripiani', 'r')
            ->join('r.posizionamenti', 'pos')
            ->groupBy('f.id')
            ->orderBy('totale', 'DESC')
            ->addOrderBy('f.lettera', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (array $riga): array => ['fila' => $riga['fila'], 'totale' => (int) $riga['totale']],
            $risultati
        );
    }
}
