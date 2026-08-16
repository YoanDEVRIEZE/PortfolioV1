<?php

namespace App\Entity;

use App\Enum\StatusEnum;
use App\Repository\CareerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Ignore;
use Vich\UploaderBundle\Mapping\Attribute as Vich;

#[ORM\Entity(repositoryClass: CareerRepository::class)]
#[Vich\Uploadable]
class Career
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 2000)]
    private ?string $content = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $position = null;

    #[ORM\Column(type : "datetime")]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $start_date = null;

    #[ORM\Column(type : "datetime", nullable: true)]
    private ?\DateTimeInterface $end_date = null;

    #[ORM\Column(type: 'string', enumType: StatusEnum::class)]
    private ?StatusEnum $status = StatusEnum::DefaultStatus;

    #[Ignore]
    #[Vich\UploadableField(mapping: 'cover_picture_career', fileNameProperty: 'cover_picture_filename')]
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
    #[Vich\UploadableField(mapping: 'job_picture_career', fileNameProperty: 'job_picture_filename')]
    #[Assert\File(
        maxSize: '10240k',
        mimeTypes: ['image/webp'],
        maxSizeMessage: 'L\'image ne doit pas dépasser 10Mo',
        mimeTypesMessage: 'L\'image doit avoir une extension .webp'
    )]
    private ?File $job_picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $job_picture_filename = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updateAt = null;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeInterface $start_date): static
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(?\DateTimeInterface $end_date): static
    {
        $this->end_date = $end_date;

        return $this;
    }

    #[Assert\IsTrue(message: 'La date de fin doit être postérieure ou égale à la date de début.')]
    public function isDateRangeValid(): bool
    {
        return null === $this->end_date || null === $this->start_date || $this->end_date >= $this->start_date;
    }

    public function getStatus(): ?string
    {
        return $this->status->value;
    }

    public function setStatus(StatusEnum $status): static
    {
        $this->status = $status;

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

    public function getCoverPictureFilename(): ?string
    {
        return $this->cover_picture_filename;
    }

    public function setCoverPictureFilename(?string $cover_picture_filename): static
    {
        $this->cover_picture_filename = $cover_picture_filename;

        return $this;
    }

    public function getJobPicture(): ?File
    {
        return $this->job_picture;
    }

    public function setJobPicture(?File $job_picture): void
    {
        $this->job_picture = $job_picture;

        if($job_picture !== null) {
            $this->updateAt = new \DateTimeImmutable();
        }        
    }

    public function getJobPictureFilename(): ?string
    {
        return $this->job_picture_filename;
    }

    public function setJobPictureFilename(?string $job_picture_filename): static
    {
        $this->job_picture_filename = $job_picture_filename;

        return $this;
    }
}
