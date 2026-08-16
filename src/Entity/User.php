<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Ignore;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[Vich\Uploadable]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse e-mail est déjà utilisée.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email(message: "L'adresse e-mail n'est pas valide.")]
    #[Assert\NotBlank(message: "L'adresse e-mail ne peut pas être vide.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "L'adresse e-mail doit contenir au moins {{ limit }} caractères.",
        maxMessage: "L'adresse e-mail ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = ['ROLE_ADMIN'];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\Length(min: 12, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.')]
    #[Assert\Regex(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{12,}$/',
        message: 'Le mot de passe doit contenir une majuscule, une minuscule et un chiffre.'
    )]
    private ?string $plainPassword = null;

    private ?string $currentPassword = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $lastname = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le prénom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le prénom ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $firstname = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(
        min: 10,
        max: 10,
        exactMessage: "Le numéro de téléphone doit contenir exactement {{ limit }} chiffres."
    )]
    #[Assert\Regex(pattern: '/^\d{10}$/', message: 'Le numéro de téléphone doit contenir uniquement 10 chiffres.')]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(requireTld: true, message: "L'URL GitHub n'est pas valide.")]
    private ?string $link_github = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(requireTld: true, message: "L'URL LinkedIn n'est pas valide.")]
    private ?string $link_linkedin = null;

    #[Ignore] 
    #[Vich\UploadableField(mapping: 'cv', fileNameProperty: 'cvFilename')]
    #[Assert\File(
        maxSize: '10240k',
        mimeTypes: ['application/pdf'],
        maxSizeMessage: 'Le fichier ne doit pas dépasser 10Mo',
        mimeTypesMessage: 'Le fichier doit avoir une extension .pdf'
    )]
    private ?File $cv = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cvfilename = null;

    #[Ignore] 
    #[Vich\UploadableField(mapping: 'profil_photo', fileNameProperty: 'photo_filename')]
    #[Assert\File(
        maxSize: '10240k',
        mimeTypes: ['image/webp'],
        maxSizeMessage: 'L\'image ne doit pas dépasser 10Mo',
        mimeTypesMessage: 'L\'image doit avoir une extension .webp'
    )]
    private ?File $photo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_filename = null;

    #[ORM\Version]
    #[ORM\Column(type: 'integer')]
    private ?int $version = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updateAt = null;

    public function __toString(): string
    {
        return $this->email ?: 'Administrateur';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower(trim($email));

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_ADMIN';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getCurrentPassword(): ?string
    {
        return $this->currentPassword;
    }

    public function setCurrentPassword(?string $currentPassword): static
    {
        $this->currentPassword = $currentPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
        $this->currentPassword = null;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getLinkGithub(): ?string
    {
        return $this->link_github;
    }

    public function setLinkGithub(?string $link_github): static
    {
        $this->link_github = $link_github;

        return $this;
    }

    public function getLinkLinkedin(): ?string
    {
        return $this->link_linkedin;
    }

    public function setLinkLinkedin(?string $link_linkedin): static
    {
        $this->link_linkedin = $link_linkedin;

        return $this;
    }

    public function getCv(): ?File
    {
        return $this->cv;
    }

    public function setCv(?File $cv): void
    {
        $this->cv = $cv;

        if($cv !== null) {
            $this->updateAt = new \DateTimeImmutable();
        }
    }

    public function getCvfilename(): ?string
    {
        return $this->cvfilename;
    }

    public function setCvfilename(?string $cvfilename): static
    {
        $this->cvfilename = $cvfilename;

        return $this;
    }

    public function getPhoto(): ?File
    {
        return $this->photo;
    }

    public function setPhoto(?File $photo): void
    {
        $this->photo = $photo;

        if($photo !== null) {
            $this->updateAt = new \DateTimeImmutable();
        }
    }

    public function getPhotoFilename(): ?string
    {
        return $this->photo_filename;
    }

    public function setPhotoFilename(?string $photo_filename): static
    {
        $this->photo_filename = $photo_filename;

        return $this;
    }

    public function markUpdated(): static
    {
        $this->updateAt = new \DateTimeImmutable();

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'roles' => $this->roles,
            'password' => $this->password,
            'lastname' => $this->lastname,
            'firstname' => $this->firstname,
            'phone' => $this->phone,
            'link_github' => $this->link_github,
            'link_linkedin' => $this->link_linkedin,
            'cvfilename' => $this->cvfilename,
            'updateAt' => $this->updateAt,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->roles = $data['roles'];
        $this->password = $data['password'];
        $this->lastname = $data['lastname'];
        $this->firstname = $data['firstname'];
        $this->phone = $data['phone'];
        $this->link_github = $data['link_github'];
        $this->link_linkedin = $data['link_linkedin'];
        $this->cvfilename = $data['cvfilename'];
        $this->updateAt = $data['updateAt'];
    }

}
