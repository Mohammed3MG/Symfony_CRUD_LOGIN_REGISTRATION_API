<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Product name cannot be blank.")]
    #[Assert\Length(min: 3, max: 255,
    minMessage: "Product name must be at least {{ limit }} characters long.",
    maxMessage: "Product name cannot be longer than {{ limit }} characters.")]
    private ?string $productname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Product price cannot be blank.")]
    #[Assert\Regex(
    pattern: '/^\d+(\.\d{1,2})?$/',
    message: "Product price must be a valid number with up to two decimal places."
    )]
    private ?string $productprice = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Product description cannot be blank.")]
    private ?string $productdescription = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "Created date cannot be blank.")]
    #[Assert\Type("\DateTimeInterface", message: "Created date must be a valid date.")]
    private ?\DateTimeInterface $createdAt = null;

    public function getId(): ?int
    {
    return $this->id;
    }

    public function getProductname(): ?string
    {
    return $this->productname;
    }

    public function setProductname(string $productname): static
    {
    $this->productname = $productname;
    return $this;
    }

    public function getProductprice(): ?string
    {
    return $this->productprice;
    }

    public function setProductprice(string $productprice): static
    {
    $this->productprice = $productprice;
    return $this;
    }

    public function getProductdescription(): string
    {
    return $this->productdescription;
    }

    public function setProductdescription(string $productdescription): static
    {
    $this->productdescription = $productdescription;
    return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
    return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
    $this->createdAt = $createdAt;
    return $this;
    }
}
