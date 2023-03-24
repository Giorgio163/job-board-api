<?php

namespace App\Entity;

use App\Repository\ApplicantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ApplicantRepository::class)]
class Applicant
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Assert\Regex(
        pattern: '/\d/',
        message: 'It should be a string',
        match: false
    )]
    #[ORM\Column(length: 255)]
    private ?string $name = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[ORM\Column(length: 255)]
    private ?string $contactInformation = null;
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[ORM\Column(length: 255)]
    private ?string $jobPreferences = null;

    #[ORM\ManyToMany(targetEntity: Job::class, mappedBy: 'applicants')]
    private Collection $jobsApplied;

    public function __construct()
    {
        $this->jobsApplied = new ArrayCollection();
    }

    public function getId(): ?Uuid
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

    public function getContactInformation(): ?string
    {
        return $this->contactInformation;
    }

    public function setContactInformation(string $contactInformation): self
    {
        $this->contactInformation = $contactInformation;

        return $this;
    }

    public function getJobPreferences(): ?string
    {
        return $this->jobPreferences;
    }

    public function setJobPreferences(string $jobPreferences): self
    {
        $this->jobPreferences = $jobPreferences;

        return $this;
    }

    /**
     * @return Collection<int, Job>
     */
    public function getJobsApplied(): Collection
    {
        return $this->jobsApplied;
    }

    public function addJobsApplied(Job $jobsApplied): self
    {
        if (!$this->jobsApplied->contains($jobsApplied)) {
            $this->jobsApplied->add($jobsApplied);
            $jobsApplied->addApplicant($this);
        }

        return $this;
    }

    public function removeJobsApplied(Job $jobsApplied): self
    {
        if ($this->jobsApplied->removeElement($jobsApplied)) {
            $jobsApplied->removeApplicant($this);
        }

        return $this;
    }
}
