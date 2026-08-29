<?php

namespace App\Entity;

use App\Repository\CategoriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoriaRepository::class)]
#[ORM\Table(name: 'categoria')]
#[ORM\UniqueConstraint(name: 'uniq_categoria_nome', columns: ['nome'])]
class Categoria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Il nome della categoria è obbligatorio.')]
    #[Assert\Length(max: 100)]
    private ?string $nome = null;

    /**
     * @var Collection<int, Materiale>
     */
    #[ORM\OneToMany(targetEntity: Materiale::class, mappedBy: 'categoria')]
    private Collection $materiali;

    public function __construct()
    {
        $this->materiali = new ArrayCollection();
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
     * @return Collection<int, Materiale>
     */
    public function getMateriali(): Collection
    {
        return $this->materiali;
    }
}
