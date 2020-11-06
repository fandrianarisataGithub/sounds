<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ClientUploadRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=ClientUploadRepository::class)
 */
class ClientUpload
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
    private $annee;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type_client;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numero_facture;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $personne_hebergee;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $montant;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $montant_payer;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_pmt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mode_pmt;

    /**
     * @ORM\ManyToMany(targetEntity=Hotel::class, inversedBy="clientUploads")
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

    public function getAnnee(): ?string
    {
        return $this->annee;
    }

    public function setAnnee(?string $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    public function getTypeClient(): ?string
    {
        return $this->type_client;
    }

    public function setTypeClient(?string $type_client): self
    {
        $this->type_client = $type_client;

        return $this;
    }

    public function getNumeroFacture(): ?string
    {
        return $this->numero_facture;
    }

    public function setNumeroFacture(?string $numero_facture): self
    {
        $this->numero_facture = $numero_facture;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPersonneHebergee(): ?string
    {
        return $this->personne_hebergee;
    }

    public function setPersonneHebergee(?string $personne_hebergee): self
    {
        $this->personne_hebergee = $personne_hebergee;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(?string $montant): self
    {
        $this->montant = $montant;

        return $this;
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

    public function getMontantPayer(): ?string
    {
        return $this->montant_payer;
    }

    public function setMontantPayer(?string $montant_payer): self
    {
        $this->montant_payer = $montant_payer;

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

    public function getModePmt(): ?string
    {
        return $this->mode_pmt;
    }

    public function setModePmt(?string $mode_pmt): self
    {
        $this->mode_pmt = $mode_pmt;

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
