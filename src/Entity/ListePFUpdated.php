<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\ListePFUpdatedRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=ListePFUpdatedRepository::class)
 */
class ListePFUpdated
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $id_pro;

    /**
     * @ORM\ManyToOne(targetEntity=ClientUpdated::class, inversedBy="listePFUpdateds")
     */
    private $clientUpdated;

    /**
     * @ORM\OneToMany(targetEntity=ChangementAfterImport::class, mappedBy="listePFUpdated")
     */
    private $changementAfterImports;

    public function __construct()
    {
        $this->changementAfterImports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdPro(): ?string
    {
        return $this->id_pro;
    }

    public function setIdPro(string $id_pro): self
    {
        $this->id_pro = $id_pro;

        return $this;
    }

    public function getClientUpdated(): ?ClientUpdated
    {
        return $this->clientUpdated;
    }

    public function setClientUpdated(?ClientUpdated $clientUpdated): self
    {
        $this->clientUpdated = $clientUpdated;

        return $this;
    }

    /**
     * @return Collection|ChangementAfterImport[]
     */
    public function getChangementAfterImports(): Collection
    {
        return $this->changementAfterImports;
    }

    public function addChangementAfterImport(ChangementAfterImport $changementAfterImport): self
    {
        if (!$this->changementAfterImports->contains($changementAfterImport)) {
            $this->changementAfterImports[] = $changementAfterImport;
            $changementAfterImport->setListePFUpdated($this);
        }

        return $this;
    }

    public function removeChangementAfterImport(ChangementAfterImport $changementAfterImport): self
    {
        if ($this->changementAfterImports->removeElement($changementAfterImport)) {
            // set the owning side to null (unless already changed)
            if ($changementAfterImport->getListePFUpdated() === $this) {
                $changementAfterImport->setListePFUpdated(null);
            }
        }

        return $this;
    }
}
