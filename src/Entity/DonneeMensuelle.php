<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DonneeMensuelleRepository;

/**
 * @ORM\Entity(repositoryClass=DonneeMensuelleRepository::class)
 */
class DonneeMensuelle
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $stock;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cost_restaurant_value;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cost_restaurant_pourcent;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cost_electricite_value;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cost_electricite_pourcent;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cost_eau_value;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cost_eau_pourcent;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cost_gasoil_value;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $cost_gasoil_pourcent;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $salaire_brute_value;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $salaire_brute_pourcent;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $sqn_interne;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $sqn_booking;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $sqn_tripadvisor;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mois;

    /**
     * @ORM\ManyToOne(targetEntity=Hotel::class, inversedBy="donneeMensuelles")
     */
    private $hotel;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $kpi_adr;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $kpi_revp;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStock(): ?string
    {
        return $this->stock;
    }

    public function setStock(?string $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getCostRestaurantValue(): ?string
    {
        return $this->cost_restaurant_value;
    }

    public function setCostRestaurantValue(?string $cost_restaurant_value): self
    {
        $this->cost_restaurant_value = $cost_restaurant_value;

        return $this;
    }

    public function getCostRestaurantPourcent(): ?string
    {
        return $this->cost_restaurant_pourcent;
    }

    public function setCostRestaurantPourcent(?string $cost_restaurant_pourcent): self
    {
        $this->cost_restaurant_pourcent = $cost_restaurant_pourcent;

        return $this;
    }

    public function getCostElectriciteValue(): ?string
    {
        return $this->cost_electricite_value;
    }

    public function setCostElectriciteValue(?string $cost_electricite_value): self
    {
        $this->cost_electricite_value = $cost_electricite_value;

        return $this;
    }

    public function getCostElectricitePourcent(): ?string
    {
        return $this->cost_electricite_pourcent;
    }

    public function setCostElectricitePourcent(?string $cost_electricite_pourcent): self
    {
        $this->cost_electricite_pourcent = $cost_electricite_pourcent;

        return $this;
    }

    public function getCostEauValue(): ?string
    {
        return $this->cost_eau_value;
    }

    public function setCostEauValue(?string $cost_eau_value): self
    {
        $this->cost_eau_value = $cost_eau_value;

        return $this;
    }

    public function getCostEauPourcent(): ?string
    {
        return $this->cost_eau_pourcent;
    }

    public function setCostEauPourcent(string $cost_eau_pourcent): self
    {
        $this->cost_eau_pourcent = $cost_eau_pourcent;

        return $this;
    }

    public function getCostGasoilValue(): ?string
    {
        return $this->cost_gasoil_value;
    }

    public function setCostGasoilValue(?string $cost_gasoil_value): self
    {
        $this->cost_gasoil_value = $cost_gasoil_value;

        return $this;
    }

    public function getCostGasoilPourcent(): ?string
    {
        return $this->cost_gasoil_pourcent;
    }

    public function setCostGasoilPourcent(?string $cost_gasoil_pourcent): self
    {
        $this->cost_gasoil_pourcent = $cost_gasoil_pourcent;

        return $this;
    }

    public function getSalaireBruteValue(): ?string
    {
        return $this->salaire_brute_value;
    }

    public function setSalaireBruteValue(?string $salaire_brute_value): self
    {
        $this->salaire_brute_value = $salaire_brute_value;

        return $this;
    }

    public function getSalaireBrutePourcent(): ?string
    {
        return $this->salaire_brute_pourcent;
    }

    public function setSalaireBrutePourcent(?string $salaire_brute_pourcent): self
    {
        $this->salaire_brute_pourcent = $salaire_brute_pourcent;

        return $this;
    }

    public function getSqnInterne(): ?string
    {
        return $this->sqn_interne;
    }

    public function setSqnInterne(?string $sqn_interne): self
    {
        $this->sqn_interne = $sqn_interne;

        return $this;
    }

    public function getSqnBooking(): ?string
    {
        return $this->sqn_booking;
    }

    public function setSqnBooking(?string $sqn_booking): self
    {
        $this->sqn_booking = $sqn_booking;

        return $this;
    }

    public function getSqnTripadvisor(): ?string
    {
        return $this->sqn_tripadvisor;
    }

    public function setSqnTripadvisor(?string $sqn_tripadvisor): self
    {
        $this->sqn_tripadvisor = $sqn_tripadvisor;

        return $this;
    }

    public function getMois(): ?string
    {
        return $this->mois;
    }

    public function setMois(string $mois): self
    {
        $this->mois = $mois;

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

    public function getKpiAdr(): ?string
    {
        return $this->kpi_adr;
    }

    public function setKpiAdr(?string $kpi_adr): self
    {
        $this->kpi_adr = $kpi_adr;

        return $this;
    }

    public function getKpiRevp(): ?string
    {
        return $this->kpi_revp;
    }

    public function setKpiRevp(?string $kpi_revp): self
    {
        $this->kpi_revp = $kpi_revp;

        return $this;
    }
}
