<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TresorerieDepenseRepository;

/**
 * @ORM\Entity(repositoryClass=TresorerieDepenseRepository::class)
 */
class TresorerieDepense extends Tresorerie
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
    private $num_compte;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom_fournisseur;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumCompte(): ?string
    {
        return $this->num_compte;
    }

    public function setNumCompte(?string $num_compte): self
    {
        $this->num_compte = $num_compte;

        return $this;
    }

    public function getNomFournisseur(): ?string
    {
        return $this->nom_fournisseur;
    }

    public function setNomFournisseur(?string $nom_fournisseur): self
    {
        $this->nom_fournisseur = $nom_fournisseur;

        return $this;
    }
}
