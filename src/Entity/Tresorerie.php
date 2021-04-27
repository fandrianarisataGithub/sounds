<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TresorerieRepository;

/**
 * @ORM\Entity(repositoryClass=TresorerieRepository::class)
 */
class Tresorerie
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
    private $designation;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_paiment;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mode_paiement;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $compte;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $encaissement;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $decaissement;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $monnaie;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type_flux;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $categorie;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sous_categorie;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $idPro;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $client;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $num_sage;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prestataire;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getDatePaiment(): ?\DateTimeInterface
    {
        return $this->date_paiment;
    }

    public function setDatePaiment(?\DateTimeInterface $date_paiment): self
    {
        $this->date_paiment = $date_paiment;

        return $this;
    }

    public function getModePaiement(): ?string
    {
        return $this->mode_paiement;
    }

    public function setModePaiement(string $mode_paiement): self
    {
        $this->mode_paiement = $mode_paiement;

        return $this;
    }

    public function getCompte(): ?string
    {
        return $this->compte;
    }

    public function setCompte(string $compte): self
    {
        $this->compte = $compte;

        return $this;
    }

    public function getEncaissement(): ?string
    {
        return $this->encaissement;
    }

    public function setEncaissement(?string $encaissement): self
    {
        $this->encaissement = $encaissement;

        return $this;
    }

    public function getDecaissement(): ?string
    {
        return $this->decaissement;
    }

    public function setDecaissement(?string $decaissement): self
    {
        $this->decaissement = $decaissement;

        return $this;
    }

    public function getMonnaie(): ?string
    {
        return $this->monnaie;
    }

    public function setMonnaie(string $monnaie): self
    {
        $this->monnaie = $monnaie;

        return $this;
    }

    public function getTypeFlux(): ?string
    {
        return $this->type_flux;
    }

    public function setTypeFlux(string $type_flux): self
    {
        $this->type_flux = $type_flux;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getSousCategorie(): ?string
    {
        return $this->sous_categorie;
    }

    public function setSousCategorie(?string $sous_categorie): self
    {
        $this->sous_categorie = $sous_categorie;

        return $this;
    }

    public function getIdPro(): ?string
    {
        return $this->idPro;
    }

    public function setIdPro(?string $idPro): self
    {
        $this->idPro = $idPro;

        return $this;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(?string $client): self
    {
        $this->client = $client;

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

    public function getPrestataire(): ?string
    {
        return $this->prestataire;
    }

    public function setPrestataire(?string $prestataire): self
    {
        $this->prestataire = $prestataire;

        return $this;
    }
}
