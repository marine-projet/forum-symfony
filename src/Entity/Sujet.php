<?php

namespace App\Entity;

use App\Repository\SujetRepository;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entité représentant un sujet.
 * 
 * Un sujet est une entité qui est associée à une catégorie et contient plusieurs commentaires.
 */
#[ORM\Entity(repositoryClass: SujetRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Sujet
{
    /**
     * Identifiant unique du sujet.
     * 
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom ou titre du sujet.
     * 
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * Date de création du sujet.
     * 
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTime $createdAt = null;

    /**
     * Catégorie à laquelle ce sujet appartient.
     * 
     * @var Category|null
     */
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'sujets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * Liste des commentaires de ce sujet.
     * 
     * @var Collection<int, Comment> Collection d'entités Comment associées à ce sujet.
     */
    #[ORM\OneToMany(mappedBy: 'subject', targetEntity: Comment::class, orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(["date" => "DESC"])]
    private Collection $comments;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'favoriteSubjects')]
    private Collection $favoritedBy;

    /**
     * Constructeur de la classe Sujet.
     * 
     * Initialise la propriété `createdAt` à l'instant de la création de l'instance
     * et initialise la collection des commentaires.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->comments = new ArrayCollection();
        $this->favoritedBy = new ArrayCollection();
    }

    /**
     * Callback exécutée avant la persistance de l'entité.
     * 
     * Permet de garantir que la date de création (`createdAt`) est correctement initialisée.
     *
     * @return void
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }

    /**
     * Récupère l'identifiant unique du sujet.
     * 
     * @return int|null L'identifiant du sujet.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère le nom ou titre du sujet.
     * 
     * @return string|null Le nom du sujet.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Définit le nom ou titre du sujet.
     * 
     * @param string $name Le nom ou titre à attribuer au sujet.
     * @return self
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Récupère la date de création du sujet.
     * 
     * @return \DateTimeInterface|null La date de création.
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Définit la date de création.
     * 
     * Cette méthode peut être utilisée pour mettre à jour ou corriger la date manuellement.
     * 
     * @param \DateTimeInterface $createdAt La nouvelle date de création.
     * @return self
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Récupère la catégorie associée au sujet.
     * 
     * @return Category|null La catégorie du sujet.
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Définit la catégorie associée au sujet.
     * 
     * @param Category|null $category La catégorie à associer.
     * @return self
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Récupère les commentaires associés au sujet.
     * 
     * @return Collection<int, Comment> Une collection de commentaires triés par date décroissante.
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * Ajoute un commentaire au sujet.
     * 
     * Associe automatiquement le sujet au commentaire ajouté.
     * 
     * @param Comment $comment Le commentaire à ajouter.
     * @return self
     */
    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setSubject($this); // Associer ce sujet au commentaire
        }

        return $this;
    }

    /**
     * Supprime un commentaire du sujet.
     * 
     * Dissocie également le commentaire du sujet.
     * 
     * @param Comment $comment Le commentaire à supprimer.
     * @return self
     */
    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // Vérifie et dissocie le commentaire du sujet
            if ($comment->getSubject() === $this) {
                $comment->setSubject(null); // Dissocier le sujet
            }
        }
        $this->favoritedBy = new ArrayCollection();

        return $this;
    }
        /**
     * @return Collection|User[]
     */
    public function getFavoritedBy(): Collection
    {
        return $this->favoritedBy;
    }

    public function addFavoritedBy(User $user): self
    {
        if (!$this->favoritedBy->contains($user)) {
            $this->favoritedBy[] = $user;
        }

        return $this;
    }

    public function removeFavoritedBy(User $user): self
    {
        $this->favoritedBy->removeElement($user);
        return $this;
    }

}