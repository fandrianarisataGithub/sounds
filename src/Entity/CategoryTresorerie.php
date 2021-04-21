<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\CategoryTresorerieRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=CategoryTresorerieRepository::class)
 */
class CategoryTresorerie
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
     * @ORM\OneToMany(targetEntity=SousCategorieTresorerie::class, mappedBy="categorie")
     */
    private $sousCategorieTresoreries;

    public function __construct()
    {
        $this->sousCategorieTresoreries = new ArrayCollection();
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
     * @return Collection|SousCategorieTresorerie[]
     */
    public function getSousCategorieTresoreries(): Collection
    {
        return $this->sousCategorieTresoreries;
    }

    public function addSousCategorieTresorery(SousCategorieTresorerie $sousCategorieTresorery): self
    {
        if (!$this->sousCategorieTresoreries->contains($sousCategorieTresorery)) {
            $this->sousCategorieTresoreries[] = $sousCategorieTresorery;
            $sousCategorieTresorery->setCategorie($this);
        }

        return $this;
    }

    public function removeSousCategorieTresorery(SousCategorieTresorerie $sousCategorieTresorery): self
    {
        if ($this->sousCategorieTresoreries->removeElement($sousCategorieTresorery)) {
            // set the owning side to null (unless already changed)
            if ($sousCategorieTresorery->getCategorie() === $this) {
                $sousCategorieTresorery->setCategorie(null);
            }
        }

        return $this;
    }
}
