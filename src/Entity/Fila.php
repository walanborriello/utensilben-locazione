<?php

namespace App\Entity;

use App\Enum\TipoFila;
use App\Repository\FilaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FilaRepository::class)]
#[ORM\Table(name: 'fila')]
#[ORM\UniqueConstraint(name: 'uniq_fila_codice', columns: ['codice'])]
class Fila
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Il codice della fila è obbligatorio.')]
    #[Assert\Length(max: 20)]
    private ?string $codice = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Il nome della fila è obbligatorio.')]
    #[Assert\Length(max: 100)]
    private ?string $nome = null;

    #[ORM\Column(length: 20, enumType: TipoFila::class)]
    #[Assert\NotNull]
    private TipoFila $tipo = TipoFila::UNICA;

    /**
     * @var Collection<int, Area>
     */
    #[ORM\OneToMany(targetEntity: Area::class, mappedBy: 'fila', cascade: ['persist'], orphanRemoval: false)]
    #[ORM\OrderBy(['lato' => 'ASC'])]
    private Collection $aree;

    public function __construct()
    {
        $this->aree = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->codice ?? '', $this->nome ?? '');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodice(): ?string
    {
        return $this->codice;
    }

    public function setCodice(string $codice): static
    {
        $this->codice = $codice;

        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getTipo(): TipoFila
    {
        return $this->tipo;
    }

    public function setTipo(TipoFila $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * @return Collection<int, Area>
     */
    public function getAree(): Collection
    {
        return $this->aree;
    }

    public function addArea(Area $area): static
    {
        if (!$this->aree->contains($area)) {
            $this->aree->add($area);
            $area->setFila($this);
        }

        return $this;
    }
}
