<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookRepository;
use App\Service\TimeStampTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks()]
#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getBook','getAuthor'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getBook','getAuthor'])]
    #[Assert\NotBlank(message: "Le Titre de l'article ne doit pas etre vide.")]
    #[Assert\Length(min:1, max:255, minMessage: "Le Titre  doit avoir moins {{limit}} caractères.")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['getBook'])]
    private ?string $description = null;

    #[Groups(['getBook'])]
    #[ORM\ManyToOne(inversedBy: 'books')]
    private ?Author $author = null;

    #[Groups(['getBook'])]
    #[ORM\ManyToOne(inversedBy: 'books')]
    private ?Category $category = null;

    #[ORM\Column]
    private ?bool $published = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function __toString()
    {
        return $this->title;
    }


    
}
