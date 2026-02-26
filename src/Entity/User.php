<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Entity\Sujet;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Entité représentant un utilisateur dans l'application.
 * 
 * Cette entité gère les informations relatives à un utilisateur, y compris ses 
 * identifiants, son statut, ses rôles, ainsi que les relations avec d'autres entités 
 * (comme ses commentaires).
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "`user`")]
#[UniqueEntity(fields: ['username'], message: 'Il existe déjà un compte avec cet identifiant.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Identifiant unique de l'utilisateur.
     *
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * URL de la photo de profil de l'utilisateur (facultatif).
     * 
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $profilePicture = null;

    /**
     * Nom d'utilisateur unique.
     * 
     * @var string|null
     */
    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    /**
     * Adresse email unique.
     * 
     * @var string|null
     */
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * Liste des rôles attribués à l'utilisateur.
     * 
     * @var array<string>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /**
     * Mot de passe encodé de l'utilisateur.
     * 
     * @var string|null
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * Indique si l'utilisateur a confirmé son compte.
     * 
     * @var bool
     */
    #[ORM\Column]
    private bool $isVerified = false;

    /**
     * Indique si l'utilisateur est actif.
     * 
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isActive = false;

    /**
     * Date de création du compte utilisateur.
     * 
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    /**
     * Les commentaires postés par cet utilisateur.
     * 
     * @var Collection<int, Comment> Collection d'objets Comment.
     */
    #[ORM\OneToMany(mappedBy: 'authorUser', targetEntity: Comment::class, cascade: ['persist', 'remove'])]
    private Collection $comments;

    /**
     * Les sujets favoris de cet utilisateur.
     * 
     * @var Collection<int, Sujet> Collection d'objets Sujet.
     */
    #[ORM\ManyToMany(targetEntity: Sujet::class, mappedBy: 'favoritedBy')]
    private Collection $favoriteSubjects;

    /**
     * Jeton de vérification pour l'email (facultatif).
     * 
     * @var string|null
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $emailVerificationToken = null;
    /**
     * Date de la dernière visite de l'utilisateur.
     * 
     * @var \DateTimeInterface|null
     */     
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastVisit = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, cascade: ['persist', 'remove'])]
    private Collection $notifications;

    /**
     * Constructeur de l'entité User.
     * 
     * Initialise la date de création et les commentaires de l'utilisateur.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable(); 
        $this->comments = new ArrayCollection();
        $this->favoriteSubjects = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    /**
     * Récupère l'identifiant unique de l'utilisateur.
     * 
     * @return int|null L'identifiant de l'utilisateur.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère l'identifiant unique pour la connexion (user identifier).
     * 
     * Conforme à UserInterface.
     *
     * @return string L'identifiant principal de l'utilisateur (le username).
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * Récupère le nom d'utilisateur.
     * 
     * @return string|null Le nom d'utilisateur.
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Définit le nom d'utilisateur.
     * 
     * @param string $username Le nouveau nom d'utilisateur.
     * @return self
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Récupère l'URL de la photo de profil de l'utilisateur.
     * 
     * @return string|null L'URL de la photo de profil.
     */
    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    /**
     * Définit l'URL de la photo de profil de l'utilisateur.
     * 
     * @param string|null $profilePicture L'URL de la nouvelle photo de profil.
     * @return self
     */
    public function setProfilePicture(?string $profilePicture): self
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    /**
     * Récupère l'email.
     * 
     * @return string|null L'email de l'utilisateur.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'email de l'utilisateur.
     * 
     * @param string $email Le nouvel email.
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Récupère les rôles attribués à l'utilisateur.
     * 
     * @return array<string> Liste unique des rôles.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // Assure que l'utilisateur a au moins le rôle utilisateur de base.
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Définit les rôles de l'utilisateur.
     * 
     * @param array $roles Liste de rôles à attribuer.
     * @return self
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Récupère le mot de passe encodé de l'utilisateur.
     * 
     * @return string|null Le mot de passe encodé.
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Définit le mot de passe encodé.
     * 
     * @param string $password Le mot de passe encodé.
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Supprime les informations sensibles (implémentation de `eraseCredentials`).
     * 
     * Actuellement, cette méthode est vide.
     */
    public function eraseCredentials(): void
    {
        // Supprimer en mémoire les données sensibles si nécessaire.
    }

    /**
     * Indique si l'utilisateur a confirmé son compte.
     * 
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * Définir si l'utilisateur est vérifié.
     * 
     * @param bool $isVerified Vérification (vrai ou faux).
     * @return self
     */
    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * Indique si l'utilisateur est actif.
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Définit si l'utilisateur est actif.
     * 
     * @param bool $isActive Statut actif (vrai ou faux).
     * @return self
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Récupère le jeton de vérification pour l'email.
     * 
     * @return string|null
     */
    public function getEmailVerificationToken(): ?string
    {
        return $this->emailVerificationToken;
    }

    /**
     * Définit le jeton de vérification pour l'email.
     * 
     * @param string|null $emailVerificationToken Le jeton à définir.
     * @return self
     */
    public function setEmailVerificationToken(?string $emailVerificationToken): self
    {
        $this->emailVerificationToken = $emailVerificationToken;

        return $this;
    }


    /**
     * Récupère la date de création du compte.
     * 
     * @return \DateTimeInterface|null Date de création.
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Définit la date de création du compte.
     * 
     * @param \DateTimeInterface $createdAt La nouvelle date.
     * @return self
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Récupère les commentaires de l'utilisateur.
     * 
     * @return Collection<int, Comment> Les commentaires associés.
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * Ajoute un commentaire à l'utilisateur.
     * 
     * @param Comment $comment Le commentaire à ajouter.
     * @return self
     */
    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthorUser($this);
        }

        return $this;
    }

    /**
     * Supprime un commentaire de l'utilisateur.
     * 
     * @param Comment $comment Le commentaire à supprimer.
     * @return self
     */
    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getAuthorUser() === $this) {
                $comment->setAuthorUser(null);
            }
        }

        return $this;
    }
    /**
     * @return Collection|Sujet[]
     */
    public function getFavoriteSubjects(): Collection
    {
        return $this->favoriteSubjects;
    }

    public function addFavoriteSubject(Sujet $sujet): self
    {
        if (!$this->favoriteSubjects->contains($sujet)) {
            $this->favoriteSubjects[] = $sujet;
            $sujet->addFavoritedBy($this);
        }

        return $this;
    }

    public function removeFavoriteSubject(Sujet $sujet): self
    {
        if ($this->favoriteSubjects->contains($sujet)) {
        $this->favoriteSubjects->removeElement($sujet);
        $sujet->removeFavoritedBy($this); // Inverse la relation
        }

        return $this;
    }
    public function getLastVisit(): ?\DateTimeInterface
    {
        return $this->lastVisit;
    }
    public function setLastVisit(?\DateTimeInterface $lastVisit): self
    {
        $this->lastVisit = $lastVisit;

        return $this;
    }
    /**
     * Obtient toutes les notifications associées à l'utilisateur.
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    /**
     * Ajoute une notification pour cet utilisateur.
     */
    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    /**
     * Supprime une notification associée à cet utilisateur.
     */
    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }
}