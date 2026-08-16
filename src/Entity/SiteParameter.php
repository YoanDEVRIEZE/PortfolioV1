<?php

namespace App\Entity;

use App\Repository\SiteParameterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SiteParameterRepository::class)]
class SiteParameter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(min: 1, max: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(min: 1, max: 255)]
    private ?string $description = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $keyword = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(min: 1, max: 255)]
    private ?string $media_description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "Le lien doit être une URL valide.")]
    #[Assert\Length(min: 1, max: 255)]    
    private ?string $url_site = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
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

    public function getKeyword(): ?array
    {
        return $this->keyword;
    }

    public function setKeyword(?array $keyword): static
    {
        $this->keyword = $keyword;

        return $this;
    }

    public function getMediaDescription(): ?string
    {
        return $this->media_description;
    }

    public function setMediaDescription(?string $media_description): static
    {
        $this->media_description = $media_description;

        return $this;
    }

    public function getUrlSite(): ?string
    {
        return $this->url_site;
    }

    public function setUrlSite(?string $url_site): static
    {
        $this->url_site = $url_site;

        return $this;
    }
}
