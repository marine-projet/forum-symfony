<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entité représentant une catégorie.
 * 
 * Une catégorie regroupe plusieurs sujets (entité `Sujet`) et peut être utilisée
 * pour organiser les sujets dans des sections logiques.
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    /**
     * Identifiant unique de la catégorie.
     * 
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    /**
     * Nom de la catégorie.
     * 
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    /**
     * Collection des sujets associés à la catégorie.
     * 
     * @var Collection<int, Sujet> Collection d'entités `Sujet`.
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Sujet::class, cascade: ['persist', 'remove'])]
    private Collection $sujets;

    /**
     * Constructeur permettant d'initialiser la collection des sujets.
     */
    public function __construct()
    {
        $this->sujets = new ArrayCollection();
    }

    /**
     * Récupère l'identifiant unique de la catégorie.
     * 
     * @return int|null L'identifiant de la catégorie.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère le nom de la catégorie.
     * 
     * @return string|null Le nom de la catégorie.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Définit le nom de la catégorie.
     * 
     * @param string $name Le nom de la catégorie.
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Récupère les sujets associés à la catégorie.
     * 
     * @return Collection<int, Sujet> Une collection des sujets de la catégorie.
     */
    public function getSujets(): Collection
    {
        return $this->sujets;
    }

    /**
     * Ajoute un sujet à la catégorie.
     * 
     * Lie automatiquement la catégorie au sujet.
     * 
     * @param Sujet $sujet Le sujet à ajouter.
     * @return self
     */
    public function addSujet(Sujet $sujet): self
    {
        if (!$this->sujets->contains($sujet)) {
            $this->sujets[] = $sujet;
            $sujet->setCategory($this);
        }

        return $this;
    }

    /**
     * Supprime un sujet de la catégorie.
     * 
     * Dissocie automatiquement la catégorie du sujet.
     * 
     * @param Sujet $sujet Le sujet à supprimer.
     * @return self
     */
    public function removeSujet(Sujet $sujet): self
    {
        if ($this->sujets->removeElement($sujet)) {
            // Vérifie la relation et dissocie
            if ($sujet->getCategory() === $this) {
                $sujet->setCategory(null);
            }
        }

        return $this;
    }
}