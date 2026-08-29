<?php

namespace App\Entity;

use App\Enum\LatoArea;
use App\Repository\AreaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AreaRepository::class)]
#[ORM\Table(name: 'area')]
#[ORM\UniqueConstraint(name: 'uniq_area_fila_lato', columns: ['fila_id', 'lato'])]
class Area
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Fila::class, inversedBy: 'aree')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Fila $fila = null;

    #[ORM\Column(length: 20, enumType: LatoArea::class)]
    private LatoArea $lato = LatoArea::UNICA;

    /**
     * @var Collection<int, Piano>
     */
    #[ORM\OneToMany(targetEntity: Piano::class, mappedBy: 'area', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['numero' => 'ASC'])]
    private Collection $piani;

    public function __construct()
    {
        $this->piani = new ArrayCollection();
    }

    public function __toString(): string
    {
        $fila = $this->fila?->getCodice() ?? '?';

        return sprintf('Fila %s - %s', $fila, $this->lato->label());
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

    public function getLato(): LatoArea
    {
        return $this->lato;
    }

    public function setLato(LatoArea $lato): static
    {
        $this->lato = $lato;

        return $this;
    }

    /**
     * @return Collection<int, Piano>
     */
    public function getPiani(): Collection
    {
        return $this->piani;
    }

    public function addPiano(Piano $piano): static
    {
        if (!$this->piani->contains($piano)) {
            $this->piani->add($piano);
            $piano->setArea($this);
        }

        return $this;
    }
}
