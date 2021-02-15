<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ClientUpdatedRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=ClientUpdatedRepository::class)
 */
class ClientUpdated
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
    private $nom;

    /**
     * @ORM\ManyToMany(targetEntity=IntervalChangePF::class, inversedBy="clientUpdateds")
     */
    private $intervalchangePF;

    /**
     * @ORM\OneToMany(targetEntity=ListePFUpdated::class, mappedBy="clientUpdated")
     */
    private $listePFUpdateds;

    public function __construct()
    {
        $this->intervalchangePF = new ArrayCollection();
        $this->listePFUpdateds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection|IntervalChangePF[]
     */
    public function getIntervalchangePF(): Collection
    {
        return $this->intervalchangePF;
    }

    public function addIntervalchangePF(IntervalChangePF $intervalchangePF): self
    {
        if (!$this->intervalchangePF->contains($intervalchangePF)) {
            $this->intervalchangePF[] = $intervalchangePF;
        }

        return $this;
    }

    public function removeIntervalchangePF(IntervalChangePF $intervalchangePF): self
    {
        $this->intervalchangePF->removeElement($intervalchangePF);

        return $this;
    }

    /**
     * @return Collection|ListePFUpdated[]
     */
    public function getListePFUpdateds(): Collection
    {
        return $this->listePFUpdateds;
    }

    public function addListePFUpdated(ListePFUpdated $listePFUpdated): self
    {
        if (!$this->listePFUpdateds->contains($listePFUpdated)) {
            $this->listePFUpdateds[] = $listePFUpdated;
            $listePFUpdated->setClientUpdated($this);
        }

        return $this;
    }

    public function removeListePFUpdated(ListePFUpdated $listePFUpdated): self
    {
        if ($this->listePFUpdateds->removeElement($listePFUpdated)) {
            // set the owning side to null (unless already changed)
            if ($listePFUpdated->getClientUpdated() === $this) {
                $listePFUpdated->setClientUpdated(null);
            }
        }

        return $this;
    }
}
