<?php

namespace App\Entity;

use App\Repository\SousCategorieTresorerieRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SousCategorieTresorerieRepository::class)
 */
class SousCategorieTresorerie
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
     * @ORM\ManyToOne(targetEntity=CategoryTresorerie::class, inversedBy="sousCategorieTresoreries")
     */
    private $categorie;

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

    public function getCategorie(): ?CategoryTresorerie
    {
        return $this->categorie;
    }

    public function setCategorie(?CategoryTresorerie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }
}
