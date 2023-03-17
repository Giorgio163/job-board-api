<?php

namespace App\Entity;

use App\Repository\JobRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: JobRepository::class)]
class Job
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $requiredSkills = null;

    #[ORM\Column(length: 255)]
    private ?string $experience = null;

    #[ORM\ManyToOne(inversedBy: 'jobPosts')]
    private ?Company $company = null;

    #[ORM\ManyToMany(targetEntity: Applicant::class, inversedBy: 'jobsApplied')]
    private Collection $applicants;

    public function __construct()
    {
        $this->applicants = new ArrayCollection();
    }

    public function getId(): ?Uuid
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

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getRequiredSkills(): ?string
    {
        return $this->requiredSkills;
    }

    public function setRequiredSkills(string $requiredSkills): self
    {
        $this->requiredSkills = $requiredSkills;

        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(string $experience): self
    {
        $this->experience = $experience;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, Applicant>
     */
    public function getApplicants(): Collection
    {
        return $this->applicants;
    }

    public function addApplicant(Applicant $applicant): self
    {
        if (!$this->applicants->contains($applicant)) {
            $this->applicants->add($applicant);
        }

        return $this;
    }

    public function removeApplicant(Applicant $applicant): self
    {
        $this->applicants->removeElement($applicant);

        return $this;
    }

    public function toArray(): array
    {
        $companyDetails = [
            'id' => $this->getCompany()->getId(),
            'name' => $this->getCompany()->getName(),
            'description' => $this->getCompany()->getDescription(),
            'location' => $this->getCompany()->getLocation(),
            'contactInformation' => $this->getCompany()->getContactInformation()
        ];

        return [
            'id' => (string)$this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'requiredSkills' => $this->getRequiredSkills(),
            'experience' => $this->getExperience(),
            'company' => $companyDetails
        ];
    }
}
