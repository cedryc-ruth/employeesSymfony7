<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table('projects')]
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Employee::class, inversedBy: 'projects')]
    #[ORM\JoinTable(name: 'emp_project')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'emp_no', referencedColumnName: 'emp_no')]
    private Collection $employees;

    #[ORM\ManyToOne(inversedBy: 'myProjects')]
    #[ORM\JoinColumn(name: 'leader', referencedColumnName: 'emp_no')]
    private ?Employee $leader = null;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
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

    /**
     * @return Collection<int, Employee>
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(Employee $employee): static
    {
        if (!$this->employees->contains($employee)) {
            $this->employees->add($employee);
        }

        return $this;
    }

    public function removeEmployee(Employee $employee): static
    {
        $this->employees->removeElement($employee);

        return $this;
    }

    public function getLeader(): ?Employee
    {
        return $this->leader;
    }

    public function setLeader(?Employee $leader): static
    {
        $this->leader = $leader;

        return $this;
    }
}
