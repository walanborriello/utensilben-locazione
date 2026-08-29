<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'posizionamento')]
#[ORM\UniqueConstraint(name: 'uniq_posizionamento_materiale_piano', columns: ['materiale_id', 'piano_id'])]
class Posizionamento
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Materiale::class, inversedBy: 'posizionamenti')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Materiale $materiale = null;

    #[ORM\ManyToOne(targetEntity: Piano::class, inversedBy: 'posizionamenti')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Piano $piano = null;

    #[ORM\Column]
    private bool $principale = false;

    /**
     * Sezione orizzontale del piano in cui si trova il materiale (es. da "A"
     * a "Z", da un estremo all'altro del piano): facoltativa, utile quando un
     * piano è largo e diviso in più punti di prelievo.
     */
    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(max: 10)]
    private ?string $sezione = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    public function __toString(): string
    {
        return sprintf('%s -> %s', (string) $this->materiale, (string) $this->piano);
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

    public function getPiano(): ?Piano
    {
        return $this->piano;
    }

    public function setPiano(?Piano $piano): static
    {
        $this->piano = $piano;

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

    public function getSezione(): ?string
    {
        return $this->sezione;
    }

    public function setSezione(?string $sezione): static
    {
        $this->sezione = $sezione;

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
