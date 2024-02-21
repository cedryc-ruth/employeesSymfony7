<?php

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table('departments')]
#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'dept_no')]
    private ?string $id = null;

    #[ORM\Column(length: 40)]
    private ?string $deptName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $roiUrl = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDeptName(): ?string
    {
        return $this->deptName;
    }

    public function setDeptName(string $deptName): static
    {
        $this->deptName = $deptName;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getRoiUrl(): ?string
    {
        return $this->roiUrl;
    }

    public function setRoiUrl(?string $roiUrl): static
    {
        $this->roiUrl = $roiUrl;

        return $this;
    }
}
