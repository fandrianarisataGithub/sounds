<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\EntrepriseTWRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=EntrepriseTWRepository::class)
 */
class EntrepriseTW
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
     * @ORM\OneToMany(targetEntity=ContactEntrepriseTW::class, mappedBy="entreprise")
     */
    private $contactEntrepriseTWs;

    /**
     * @ORM\OneToMany(targetEntity=RemarqueEntrepriseTW::class, mappedBy="entreprise")
     */
    private $remarqueEntrepriseTWs;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adresse;

    public function __construct()
    {
        $this->contactEntrepriseTWs = new ArrayCollection();
        $this->remarqueEntrepriseTWs = new ArrayCollection();
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
     * @return Collection|ContactEntrepriseTW[]
     */
    public function getContactEntrepriseTWs(): Collection
    {
        return $this->contactEntrepriseTWs;
    }

    public function addContactEntrepriseTW(ContactEntrepriseTW $contactEntrepriseTW): self
    {
        if (!$this->contactEntrepriseTWs->contains($contactEntrepriseTW)) {
            $this->contactEntrepriseTWs[] = $contactEntrepriseTW;
            $contactEntrepriseTW->setEntreprise($this);
        }

        return $this;
    }

    public function removeContactEntrepriseTW(ContactEntrepriseTW $contactEntrepriseTW): self
    {
        if ($this->contactEntrepriseTWs->removeElement($contactEntrepriseTW)) {
            // set the owning side to null (unless already changed)
            if ($contactEntrepriseTW->getEntreprise() === $this) {
                $contactEntrepriseTW->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RemarqueEntrepriseTW[]
     */
    public function getRemarqueEntrepriseTWs(): Collection
    {
        return $this->remarqueEntrepriseTWs;
    }

    public function addRemarqueEntrepriseTW(RemarqueEntrepriseTW $remarqueEntrepriseTW): self
    {
        if (!$this->remarqueEntrepriseTWs->contains($remarqueEntrepriseTW)) {
            $this->remarqueEntrepriseTWs[] = $remarqueEntrepriseTW;
            $remarqueEntrepriseTW->setEntreprise($this);
        }

        return $this;
    }

    public function removeRemarqueEntrepriseTW(RemarqueEntrepriseTW $remarqueEntrepriseTW): self
    {
        if ($this->remarqueEntrepriseTWs->removeElement($remarqueEntrepriseTW)) {
            // set the owning side to null (unless already changed)
            if ($remarqueEntrepriseTW->getEntreprise() === $this) {
                $remarqueEntrepriseTW->setEntreprise(null);
            }
        }

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }
}
