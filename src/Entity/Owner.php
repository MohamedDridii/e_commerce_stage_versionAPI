<?php

namespace App\Entity;

use App\Repository\OwnerRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OwnerRepository::class)]
class Owner extends User
{
    
    public function __construct()
    {
        parent:: __construct();
        $this->setRoles(['ROLE_OWNER']);
        $this->setCreatedAt(new DateTimeImmutable());
    }
    

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
