<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ChangementAfterImportRepository;

/**
 * @ORM\Entity(repositoryClass=ChangementAfterImportRepository::class)
 */
class ChangementAfterImport
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $last_data;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $next_data;

    /**
     * @ORM\ManyToOne(targetEntity=ListePFUpdated::class, inversedBy="changementAfterImports")
     */
    private $listePFUpdated;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getLastData(): ?string
    {
        return $this->last_data;
    }

    public function setLastData(?string $last_data): self
    {
        $this->last_data = $last_data;

        return $this;
    }

    public function getNextData(): ?string
    {
        return $this->next_data;
    }

    public function setNextData(?string $next_data): self
    {
        $this->next_data = $next_data;

        return $this;
    }

    public function getListePFUpdated(): ?ListePFUpdated
    {
        return $this->listePFUpdated;
    }

    public function setListePFUpdated(?ListePFUpdated $listePFUpdated): self
    {
        $this->listePFUpdated = $listePFUpdated;

        return $this;
    }
}
