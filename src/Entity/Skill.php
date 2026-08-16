<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Ignore;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[Vich\Uploadable]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 100)]
    private ?int $level = null;

    #[Ignore]
    #[Vich\UploadableField(mapping: 'skill', fileNameProperty: 'logo_filename')]
    #[Assert\File(
        maxSize: '10240k',
        mimeTypes: ['image/webp'],
        maxSizeMessage: 'L\'image ne doit pas dépasser 10Mo',
        mimeTypesMessage: 'L\'image doit avoir une extension .webp'
    )]
    private ?File $logo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo_filename = null;

    #[ORM\Column(length: 7)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', message: 'La couleur doit être au format HEX (#RRGGBB ou #RGB).')]
    private ?string $color = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updateAt = null;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'skills')]
    private Collection $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getLogo(): ?File
    {
        return $this->logo;
    }

    public function setLogo(?File $logo): void
    {
        $this->logo = $logo;

        if($logo !== null) {
            $this->updateAt = new \DateTimeImmutable();
        }
    }

    public function getLogoFilename(): ?string
    {
        return $this->logo_filename ?: '';
    }

    public function setLogoFilename(?string $logo_filename): static
    {
        $this->logo_filename = $logo_filename;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if(!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->addSkill($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if($this->projects->removeElement($project)) {
            $project->removeSkill($this);
        }

        return $this;
    }
}
