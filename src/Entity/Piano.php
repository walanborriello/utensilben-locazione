<?php

namespace App\Entity;

use App\Repository\PianoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PianoRepository::class)]
#[ORM\Table(name: 'piano')]
#[ORM\UniqueConstraint(name: 'uniq_piano_area_numero', columns: ['area_id', 'numero'])]
class Piano
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Area::class, inversedBy: 'piani')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Area $area = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'Il numero del piano deve essere maggiore di zero.')]
    private ?int $numero = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $etichetta = null;

    /**
     * @var Collection<int, Posizionamento>
     */
    #[ORM\OneToMany(targetEntity: Posizionamento::class, mappedBy: 'piano', orphanRemoval: true)]
    private Collection $posizionamenti;

    public function __construct()
    {
        $this->posizionamenti = new ArrayCollection();
    }

    public function __toString(): string
    {
        $area = (string) $this->area;

        return sprintf('%s - Piano %s', $area, $this->numero ?? '?');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArea(): ?Area
    {
        return $this->area;
    }

    public function setArea(?Area $area): static
    {
        $this->area = $area;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getEtichetta(): ?string
    {
        return $this->etichetta;
    }

    public function setEtichetta(?string $etichetta): static
    {
        $this->etichetta = $etichetta;

        return $this;
    }

    /**
     * @return Collection<int, Posizionamento>
     */
    public function getPosizionamenti(): Collection
    {
        return $this->posizionamenti;
    }
}
