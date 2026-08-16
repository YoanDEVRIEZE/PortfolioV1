<?php

namespace App\Entity;

use App\Enum\StatusEnum;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Ignore;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[Vich\Uploadable]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100) ]
    private ?string $title = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(min: 2, max: 150) ]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 2000) ]
    private ?string $content = null;

    #[Ignore]
    #[Vich\UploadableField(mapping: 'cover_picture_project', fileNameProperty: 'cover_picture_filename')]
    #[Assert\File(
        maxSize: '10240k',
        mimeTypes: ['image/webp'],
        maxSizeMessage: 'L\'image ne doit pas dépasser 10Mo',
        mimeTypesMessage: 'L\'image doit avoir une extension .webp'
    )]
    private ?File $cover_picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cover_picture_filename = null;

    #[Ignore]
    #[Vich\UploadableField(mapping: 'project_picture_project', fileNameProperty: 'project_picture_filename')]
    #[Assert\File(
        maxSize: '10240k',
        mimeTypes: ['image/webp'],
        maxSizeMessage: 'L\'image ne doit pas dépasser 10Mo',
        mimeTypesMessage: 'L\'image doit avoir une extension .webp'
    )]
    private ?File $project_picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $project_picture_filename = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: 'Le lien doit être une URL valide.')]
    #[Assert\Length(min: 2, max: 255) ]
    private ?string $link = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updateAt = null;

    /**
     * @var Collection<int, Skill>
     */
    #[ORM\ManyToMany(targetEntity: Skill::class, inversedBy: 'projects')]
    #[Assert\Count(min: 1, minMessage: 'Sélectionnez au moins une compétence avant d’enregistrer le projet.')]
    private Collection $skills;

    #[ORM\Column(type: 'string', enumType: StatusEnum::class)]
    private StatusEnum $status = StatusEnum::DefaultStatus;

    public function __construct()
    {
        $this->skills = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCoverPicturefilename(): ?string
    {
        return $this->cover_picture_filename;
    }

    public function setCoverPictureFilename(?string $cover_picture_filename): static
    {
        $this->cover_picture_filename = $cover_picture_filename;

        return $this;
    }

    public function getCoverPicture(): ?File
    {
        return $this->cover_picture;
    }

    public function setCoverPicture(?File $cover_picture): void
    {
        $this->cover_picture = $cover_picture;

        if($cover_picture !== null) {
            $this->updateAt = new \DateTimeImmutable();
        }
    }

    public function getProjectPictureFilename(): ?string
    {
        return $this->project_picture_filename;
    }

    public function setProjectPictureFilename(?string $project_picture_filename): static
    {
        $this->project_picture_filename = $project_picture_filename;

        return $this;
    }

    public function getProjectPicture(): ?File
    {
        return $this->project_picture;
    }

    public function setProjectPicture(?File $project_picture): void
    {
        $this->project_picture = $project_picture;

        if($project_picture !== null) {
            $this->updateAt = new \DateTimeImmutable();
        }
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if(!$this->skills->contains($skill)) {
            $this->skills->add($skill);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        $this->skills->removeElement($skill);

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status->value;
    }

    public function setStatus(StatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }
}
