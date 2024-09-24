<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Users;

#[ORM\Entity(repositoryClass: 'App\Repository\UserSessionRepository')]
class UserSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $token;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $expiresAt;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private $user;

    public function getId(): ?int
    {
    return $this->id;
    }

    public function getToken(): ?string
    {
    return $this->token;
    }

    public function setToken(string $token): self
    {
    $this->token = $token;

    return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
    return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
    $this->createdAt = $createdAt;

    return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
    return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): self
    {
    $this->expiresAt = $expiresAt;

    return $this;
    }

    public function getUser(): ?Users
    {
    return $this->user;
    }

    public function setUser(?Users $user): self
    {
    $this->user = $user;

    return $this;
    }
}
