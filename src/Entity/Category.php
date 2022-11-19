<?php

namespace App\Entity;

use DateTimeImmutable;
use App\Service\TimeStampTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks()]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    use TimeStampTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getCategory', 'getBook'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCategory','getBook'])]
    #[Assert\NotBlank(message: "La catégorie ne doit pas etre vide.")]
    #[Assert\Length(min:2, max:50, minMessage: "La catégorie ne doit conteir au moins {{limit}} caractères .")]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['getCategory'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['getCategory'])]
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Book::class)]
    private Collection $books;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;


    public function __construct()
    {
        $this->books = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): self
    {
        $this->book = $book;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
            $book->setCategory($this);
        }

        return $this;
    }

    public function removeBook(Book $book): self
    {
        if ($this->books->removeElement($book)) {
            // set the owning side to null (unless already changed)
            if ($book->getCategory() === $this) {
                $book->setCategory(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

}
