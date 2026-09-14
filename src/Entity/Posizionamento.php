<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'posizionamento')]
#[ORM\UniqueConstraint(name: 'uniq_posizionamento_materiale_ripiano', columns: ['materiale_id', 'ripiano_id'])]
class Posizionamento
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Materiale::class, inversedBy: 'posizionamenti')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Materiale $materiale = null;

    #[ORM\ManyToOne(targetEntity: Ripiano::class, inversedBy: 'posizionamenti')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Ripiano $ripiano = null;

    #[ORM\Column]
    private bool $principale = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    public function __toString(): string
    {
        return sprintf('%s -> %s', (string) $this->materiale, (string) $this->ripiano);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMateriale(): ?Materiale
    {
        return $this->materiale;
    }

    public function setMateriale(?Materiale $materiale): static
    {
        $this->materiale = $materiale;

        return $this;
    }

    public function getRipiano(): ?Ripiano
    {
        return $this->ripiano;
    }

    public function setRipiano(?Ripiano $ripiano): static
    {
        $this->ripiano = $ripiano;

        return $this;
    }

    public function isPrincipale(): bool
    {
        return $this->principale;
    }

    public function setPrincipale(bool $principale): static
    {
        $this->principale = $principale;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }
}
