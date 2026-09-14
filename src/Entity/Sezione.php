<?php

namespace App\Entity;

use App\Repository\SezioneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Sezione (lettera minuscola a-g) di una Fila. Non si crea mai a mano: viene
 * generata automaticamente da FilaSezioneSynchronizer quando si salva la
 * Fila a cui appartiene.
 */
#[ORM\Entity(repositoryClass: SezioneRepository::class)]
#[ORM\Table(name: 'sezione')]
#[ORM\UniqueConstraint(name: 'uniq_sezione_fila_lettera', columns: ['fila_id', 'lettera'])]
class Sezione
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Fila::class, inversedBy: 'sezioni')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Fila $fila = null;

    #[ORM\Column(length: 1)]
    private ?string $lettera = null;

    /**
     * @var Collection<int, Ripiano>
     */
    #[ORM\OneToMany(targetEntity: Ripiano::class, mappedBy: 'sezione', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['numero' => 'ASC'])]
    private Collection $ripiani;

    public function __construct()
    {
        $this->ripiani = new ArrayCollection();
    }

    public function __toString(): string
    {
        $fila = (string) $this->fila;

        return sprintf('%s - Sezione %s', $fila, $this->lettera ?? '?');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFila(): ?Fila
    {
        return $this->fila;
    }

    public function setFila(?Fila $fila): static
    {
        $this->fila = $fila;

        return $this;
    }

    public function getLettera(): ?string
    {
        return $this->lettera;
    }

    public function setLettera(string $lettera): static
    {
        $this->lettera = $lettera;

        return $this;
    }

    /**
     * @return Collection<int, Ripiano>
     */
    public function getRipiani(): Collection
    {
        return $this->ripiani;
    }

    public function addRipiano(Ripiano $ripiano): static
    {
        if (!$this->ripiani->contains($ripiano)) {
            $this->ripiani->add($ripiano);
            $ripiano->setSezione($this);
        }

        return $this;
    }
}
