<?php

namespace App\Entity;

use App\Repository\PianoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Piano dell'edificio (es. "Terra", "Soppalco") — il livello più alto della
 * struttura del magazzino, sopra la Fila.
 */
#[ORM\Entity(repositoryClass: PianoRepository::class)]
#[ORM\Table(name: 'piano')]
#[ORM\UniqueConstraint(name: 'uniq_piano_nome', columns: ['nome'])]
class Piano
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Il nome del piano è obbligatorio.')]
    #[Assert\Length(max: 100)]
    private ?string $nome = null;

    /**
     * @var Collection<int, Fila>
     */
    #[ORM\OneToMany(targetEntity: Fila::class, mappedBy: 'piano', cascade: ['persist'])]
    #[ORM\OrderBy(['lettera' => 'ASC'])]
    private Collection $file;

    public function __construct()
    {
        $this->file = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->nome ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Fila>
     */
    public function getFile(): Collection
    {
        return $this->file;
    }

    public function addFila(Fila $fila): static
    {
        if (!$this->file->contains($fila)) {
            $this->file->add($fila);
            $fila->setPiano($this);
        }

        return $this;
    }
}
