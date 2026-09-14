<?php

namespace App\Entity;

use App\Repository\FilaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FilaRepository::class)]
#[ORM\Table(name: 'fila')]
#[ORM\UniqueConstraint(name: 'uniq_fila_piano_lettera', columns: ['piano_id', 'lettera'])]
class Fila
{
    /**
     * Lettere di fila ammesse nel magazzino reale (niente J/K).
     */
    public const LETTERE_VALIDE = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'L'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Piano::class, inversedBy: 'file')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Piano $piano = null;

    #[ORM\Column(length: 1)]
    #[Assert\Choice(choices: self::LETTERE_VALIDE, message: 'Lettera di fila non valida.')]
    private ?string $lettera = null;

    /**
     * Prima fila del magazzino (es. "A"): un solo lato accessibile, muro
     * dietro. Non influisce sulle sezioni generate (salta comunque la "d"
     * come tutte le file tranne l'ultima), solo un'informazione strutturale.
     */
    #[ORM\Column]
    private bool $primaFila = false;

    /**
     * Ultima fila del magazzino (es. "L"): qui il corridoio finisce, quindi
     * a differenza di tutte le altre file ha anche la sezione "d".
     */
    #[ORM\Column]
    private bool $ultimaFila = false;

    /**
     * @var Collection<int, Sezione>
     */
    #[ORM\OneToMany(targetEntity: Sezione::class, mappedBy: 'fila', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['lettera' => 'ASC'])]
    private Collection $sezioni;

    public function __construct()
    {
        $this->sezioni = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf('%s - Fila %s', (string) $this->piano, $this->lettera ?? '?');
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLettera(): ?string
    {
        return $this->lettera;
    }

    public function setLettera(string $lettera): static
    {
        $this->lettera = $lettera;

        return $this;
    }

    public function isPrimaFila(): bool
    {
        return $this->primaFila;
    }

    public function setPrimaFila(bool $primaFila): static
    {
        $this->primaFila = $primaFila;

        return $this;
    }

    public function isUltimaFila(): bool
    {
        return $this->ultimaFila;
    }

    public function setUltimaFila(bool $ultimaFila): static
    {
        $this->ultimaFila = $ultimaFila;

        return $this;
    }

    /**
     * @return Collection<int, Sezione>
     */
    public function getSezioni(): Collection
    {
        return $this->sezioni;
    }

    public function addSezione(Sezione $sezione): static
    {
        if (!$this->sezioni->contains($sezione)) {
            $this->sezioni->add($sezione);
            $sezione->setFila($this);
        }

        return $this;
    }

    public function removeSezione(Sezione $sezione): static
    {
        $this->sezioni->removeElement($sezione);

        return $this;
    }
}
