<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 */
class Client
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prenom;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateArrivee;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateDepart;

    /**
     * @ORM\Column(type="integer")
     */
    private $dureeSejour;

    /**
     * @ORM\ManyToOne(targetEntity=Hotel::class, inversedBy="clients")
     * @ORM\JoinColumn(nullable=false)
     */
    private $hotel;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbr_chambre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prix_total;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $provenance;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tarif;

    /**
     * @ORM\ManyToOne(targetEntity=Fidelisation::class, inversedBy="client")
     */
    private $fidelisation;

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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getDateArrivee(): ?\DateTimeInterface
    {
        return $this->dateArrivee;
    }

    public function setDateArrivee(\DateTimeInterface $dateArrivee): self
    {
        $this->dateArrivee = $dateArrivee;

        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTimeInterface $dateDepart): self
    {
        $this->dateDepart = $dateDepart;

        return $this;
    }

    public function getDureeSejour(): ?int
    {
        return $this->dureeSejour;
    }

    public function setDureeSejour(int $dureeSejour): self
    {
        $this->dureeSejour = $dureeSejour;

        return $this;
    }

    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function setHotel(?Hotel $hotel): self
    {
        $this->hotel = $hotel;

        return $this;
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

    public function getNbrChambre(): ?int
    {
        return $this->nbr_chambre;
    }

    public function setNbrChambre(?int $nbr_chambre): self
    {
        $this->nbr_chambre = $nbr_chambre;

        return $this;
    }

    public function getPrixTotal(): ?string
    {
        return $this->prix_total;
    }

    public function setPrixTotal(?string $prix_total): self
    {
        $this->prix_total = $prix_total;

        return $this;
    }

    public function getProvenance(): ?string
    {
        return $this->provenance;
    }

    public function setProvenance(?string $provenance): self
    {
        $this->provenance = $provenance;

        return $this;
    }

    public function getTarif(): ?string
    {
        return $this->tarif;
    }

    public function setTarif(?string $tarif): self
    {
        $this->tarif = $tarif;

        return $this;
    }

    public function getFidelisation(): ?Fidelisation
    {
        return $this->fidelisation;
    }

    public function setFidelisation(?Fidelisation $fidelisation): self
    {
        $this->fidelisation = $fidelisation;

        return $this;
    }
}
