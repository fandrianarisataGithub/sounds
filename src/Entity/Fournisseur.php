<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\FournisseurRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=FournisseurRepository::class)
 */
class Fournisseur
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numero_facture;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom_fournisseur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $montant;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $echeance;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mode_pmt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $montant_paye;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_pmt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $remarque;

    /**
     * @ORM\ManyToMany(targetEntity=Hotel::class, inversedBy="fournisseurs")
     */
    private $hotel;


    public function __construct()
    {
        $this->hotel = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getNumeroFacture(): ?string
    {
        return $this->numero_facture;
    }

    public function setNumeroFacture(string $numero_facture): self
    {
        $this->numero_facture = $numero_facture;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getEcheance(): ?\DateTimeInterface
    {
        return $this->echeance;
    }

    public function setEcheance(?\DateTimeInterface $echeance): self
    {
        $this->echeance = $echeance;

        return $this;
    }

    public function getModePmt(): ?string
    {
        return $this->mode_pmt;
    }

    public function setModePmt(string $mode_pmt): self
    {
        $this->mode_pmt = $mode_pmt;

        return $this;
    }

    public function getMontantPaye(): ?string
    {
        return $this->montant_paye;
    }

    public function setMontantPaye(?string $montant_paye): self
    {
        $this->montant_paye = $montant_paye;

        return $this;
    }

    public function getDatePmt(): ?\DateTimeInterface
    {
        return $this->date_pmt;
    }

    public function setDatePmt(?\DateTimeInterface $date_pmt): self
    {
        $this->date_pmt = $date_pmt;

        return $this;
    }

    public function getRemarque(): ?string
    {
        return $this->remarque;
    }

    public function setRemarque(?string $remarque): self
    {
        $this->remarque = $remarque;

        return $this;
    }

    /**
     * @return Collection|Hotel[]
     */
    public function getHotel(): Collection
    {
        return $this->hotel;
    }

    public function addHotel(Hotel $hotel): self
    {
        if (!$this->hotel->contains($hotel)) {
            $this->hotel[] = $hotel;
        }

        return $this;
    }

    public function removeHotel(Hotel $hotel): self
    {
        if ($this->hotel->contains($hotel)) {
            $this->hotel->removeElement($hotel);
        }

        return $this;
    }
}
