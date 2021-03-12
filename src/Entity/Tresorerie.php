<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TresorerieRepository;


class Tresorerie
{

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $designation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $num_sage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $mode_paiement;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $compte_bancaire;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $Monnaie;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $paiement;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getNumSage(): ?string
    {
        return $this->num_sage;
    }

    public function setNumSage(?string $num_sage): self
    {
        $this->num_sage = $num_sage;

        return $this;
    }

    public function getModePaiement(): ?string
    {
        return $this->mode_paiement;
    }

    public function setModePaiement(?string $mode_paiement): self
    {
        $this->mode_paiement = $mode_paiement;

        return $this;
    }

    public function getCompteBancaire(): ?string
    {
        return $this->compte_bancaire;
    }

    public function setCompteBancaire(?string $compte_bancaire): self
    {
        $this->compte_bancaire = $compte_bancaire;

        return $this;
    }

    public function getMonnaie(): ?string
    {
        return $this->Monnaie;
    }

    public function setMonnaie(?string $Monnaie): self
    {
        $this->Monnaie = $Monnaie;

        return $this;
    }

    public function getPaiement(): ?float
    {
        return $this->paiement;
    }

    public function setPaiement(?float $paiement): self
    {
        $this->paiement = $paiement;

        return $this;
    }

    public function getDepense(): ?TresorerieDepense
    {
        return $this->depense;
    }

    public function setDepense(?TresorerieDepense $depense): self
    {
        $this->depense = $depense;

        return $this;
    }

    public function getRecette(): ?TresorerieRecette
    {
        return $this->recette;
    }

    public function setRecette(?TresorerieRecette $recette): self
    {
        $this->recette = $recette;

        return $this;
    }
}
