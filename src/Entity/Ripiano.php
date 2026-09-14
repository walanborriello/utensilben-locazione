<?php

namespace App\Entity;

use App\Repository\RipianoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RipianoRepository::class)]
#[ORM\Table(name: 'ripiano')]
#[ORM\UniqueConstraint(name: 'uniq_ripiano_sezione_numero', columns: ['sezione_id', 'numero'])]
class Ripiano
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Sezione::class, inversedBy: 'ripiani')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Sezione $sezione = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'Il numero del ripiano deve essere maggiore di zero.')]
    private ?int $numero = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $etichetta = null;

    /**
     * @var Collection<int, Posizionamento>
     */
    #[ORM\OneToMany(targetEntity: Posizionamento::class, mappedBy: 'ripiano', orphanRemoval: true)]
    private Collection $posizionamenti;

    public function __construct()
    {
        $this->posizionamenti = new ArrayCollection();
    }

    public function __toString(): string
    {
        $sezione = (string) $this->sezione;

        return sprintf('%s - Ripiano %s', $sezione, $this->numero ?? '?');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSezione(): ?Sezione
    {
        return $this->sezione;
    }

    public function setSezione(?Sezione $sezione): static
    {
        $this->sezione = $sezione;

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
