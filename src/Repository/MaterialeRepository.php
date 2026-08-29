<?php

namespace App\Repository;

use App\Entity\Materiale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Materiale>
 */
class MaterialeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Materiale::class);
    }

    /**
     * Cerca i materiali attivi per nome o codice articolo. Usata
     * dall'autocompletamento della ricerca.
     *
     * @return Materiale[]
     */
    public function cerca(string $termine, int $limite = 10): array
    {
        $termine = trim($termine);
        if ('' === $termine) {
            return [];
        }

        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.attivo = true')
            ->andWhere('m.nome LIKE :termine OR m.codice LIKE :termine')
            ->setParameter('termine', '%'.$termine.'%')
            ->orderBy('m.nome', 'ASC')
            ->setMaxResults($limite);

        return $qb->getQuery()->getResult();
    }

    /**
     * Materiali attivi senza alcuna posizione assegnata: utile dopo un
     * import del catalogo, per sapere cosa va ancora collocato a scaffale.
     *
     * @return Materiale[]
     */
    public function senzaPosizione(): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.posizionamenti', 'p')
            ->andWhere('m.attivo = true')
            ->andWhere('p.id IS NULL')
            ->orderBy('m.nome', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Conta i materiali attivi senza alcuna posizione assegnata (usato nella
     * dashboard, dove serve solo il numero e non l'elenco completo).
     */
    public function contaSenzaPosizione(): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->leftJoin('m.posizionamenti', 'p')
            ->andWhere('m.attivo = true')
            ->andWhere('p.id IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Elenco filtrato di materiali per la pagina di gestione (ricerca libera
     * su nome/codice, filtro per categoria e per stato attivo/non attivo).
     *
     * @return Materiale[]
     */
    public function filtra(?string $q, ?int $categoriaId, ?bool $attivo): array
    {
        $qb = $this->createQueryBuilder('m')->orderBy('m.nome', 'ASC');

        if (null !== $q && '' !== trim($q)) {
            $qb->andWhere('m.nome LIKE :q OR m.codice LIKE :q')->setParameter('q', '%'.trim($q).'%');
        }

        if (null !== $categoriaId) {
            $qb->andWhere('m.categoria = :categoriaId')->setParameter('categoriaId', $categoriaId);
        }

        if (null !== $attivo) {
            $qb->andWhere('m.attivo = :attivo')->setParameter('attivo', $attivo);
        }

        return $qb->getQuery()->getResult();
    }
}
