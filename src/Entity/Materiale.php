<?php

namespace App\Entity;

use App\Repository\MaterialeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MaterialeRepository::class)]
#[ORM\Table(name: 'materiale')]
#[ORM\UniqueConstraint(name: 'uniq_materiale_codice', columns: ['codice'])]
#[ORM\Index(name: 'idx_materiale_nome', columns: ['nome'])]
class Materiale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Il codice articolo è obbligatorio.')]
    #[Assert\Length(max: 50)]
    private ?string $codice = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: 'Il nome del materiale è obbligatorio.')]
    #[Assert\Length(max: 150)]
    private ?string $nome = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descrizione = null;

    #[ORM\ManyToOne(targetEntity: Categoria::class, inversedBy: 'materiali')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Categoria $categoria = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $immagine = null;

    #[ORM\Column]
    private bool $attivo = true;

    /**
     * @var Collection<int, Posizionamento>
     */
    #[ORM\OneToMany(targetEntity: Posizionamento::class, mappedBy: 'materiale', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $posizionamenti;

    public function __construct()
    {
        $this->posizionamenti = new ArrayCollection();
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

    public function getDescrizione(): ?string
    {
        return $this->descrizione;
    }

    public function setDescrizione(?string $descrizione): static
    {
        $this->descrizione = $descrizione;

        return $this;
    }

    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(?Categoria $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

    public function getImmagine(): ?string
    {
        return $this->immagine;
    }

    public function setImmagine(?string $immagine): static
    {
        $this->immagine = $immagine;

        return $this;
    }

    public function isAttivo(): bool
    {
        return $this->attivo;
    }

    public function setAttivo(bool $attivo): static
    {
        $this->attivo = $attivo;

        return $this;
    }

    /**
     * @return Collection<int, Posizionamento>
     */
    public function getPosizionamenti(): Collection
    {
        return $this->posizionamenti;
    }

    public function addPosizionamento(Posizionamento $posizionamento): static
    {
        if (!$this->posizionamenti->contains($posizionamento)) {
            $this->posizionamenti->add($posizionamento);
            $posizionamento->setMateriale($this);
        }

        return $this;
    }

    public function removePosizionamento(Posizionamento $posizionamento): static
    {
        $this->posizionamenti->removeElement($posizionamento);

        return $this;
    }

    /**
     * Alias per l'inflector inglese di Symfony Form: da "posizionamenti"
     * indovina il singolare latino "posizionamentus" invece di
     * "posizionamento". Senza questo alias il CollectionType incorporato nel
     * form del prodotto non trova un adder/remover valido e va in errore.
     */
    public function addPosizionamentus(Posizionamento $posizionamento): static
    {
        return $this->addPosizionamento($posizionamento);
    }

    public function removePosizionamentus(Posizionamento $posizionamento): static
    {
        return $this->removePosizionamento($posizionamento);
    }
}
