<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant un commentaire.
 * 
 * Un commentaire est associé à un sujet, et potentiellement à un utilisateur en tant qu'auteur.
 */
#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    /**
     * Identifiant unique du commentaire.
     * 
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Contenu textuel du commentaire.
     * 
     * @var string|null
     */
    #[ORM\Column(type: 'text')]
    private ?string $text = null;

    /**
     * Nom de l'auteur du commentaire (pour les utilisateurs non enregistrés).
     * 
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $author = null;

    /**
     * Date et heure de publication du commentaire.
     * 
     * @var \DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $date = null;

    /**
     * Sujet auquel le commentaire est lié.
     * 
     * @var Sujet|null
     */
    #[ORM\ManyToOne(targetEntity: Sujet::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sujet $subject = null;

    /**
     * Utilisateur qui a écrit le commentaire (s'il est enregistré).
     * 
     * @var User|null
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?User $authorUser = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isRead = false;

    // === Getters et setters ===

    /**
     * Récupère l'identifiant unique du commentaire.
     * 
     * @return int|null L'id du commentaire.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère le contenu textuel du commentaire.
     * 
     * @return string|null Le texte du commentaire.
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * Définit le contenu textuel du commentaire.
     * 
     * @param string $text Le contenu du commentaire.
     * @return self
     */
    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Récupère le nom de l'auteur (pour les utilisateurs non enregistrés).
     * 
     * @return string|null Le nom de l'auteur.
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * Définit le nom de l'auteur.
     * 
     * @param string $author Le nom de l'auteur.
     * @return self
     */
    public function setAuthor(string $author): self
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Récupère la date et l'heure de publication du commentaire.
     * 
     * @return \DateTimeImmutable|null La date de publication.
     */
    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * Définit la date et l'heure de publication du commentaire.
     * 
     * @param \DateTimeImmutable $date La date de publication.
     * @return self
     */
    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Récupère le sujet associé au commentaire.
     * 
     * @return Sujet|null Le sujet du commentaire.
     */
    public function getSubject(): ?Sujet
    {
        return $this->subject;
    }

    /**
     * Définit le sujet associé au commentaire.
     * 
     * @param Sujet|null $subject Le sujet à associer.
     * @return self
     */
    public function setSubject(?Sujet $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Récupère l'utilisateur qui a écrit le commentaire.
     * 
     * @return User|null L'utilisateur auteur du commentaire.
     */
    public function getAuthorUser(): ?User
    {
        return $this->authorUser;
    }

    /**
     * Définit l'utilisateur qui a écrit le commentaire.
     * 
     * @param User|null $user L'utilisateur auteur.
     * @return self
     */
    public function setAuthorUser(?User $user): self
    {
        $this->authorUser = $user;
        return $this;
    }
    public function isIsRead(): bool
    {
        return $this->isRead;
    }
    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }
}