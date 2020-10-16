<?php

namespace App\Entity;

use App\Repository\FicheHotelRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FicheHotelRepository::class)
 */
class FicheHotel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $c_prestige;

    /**
     * @ORM\Column(type="integer")
     */
    private $s_familliale;

    /**
     * @ORM\Column(type="integer")
     */
    private $c_deluxe;

    /**
     * @ORM\Column(type="integer")
     */
    private $s_vip;

    /**
     * @ORM\Column(type="integer")
     */
    private $le_nautile;

    /**
     * @ORM\Column(type="integer")
     */
    private $sunset_view;

    /**
     * @ORM\OneToOne(targetEntity=Hotel::class, cascade={"persist", "remove"})
     */
    private $hotel;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCPrestige(): ?int
    {
        return $this->c_prestige;
    }

    public function setCPrestige(int $c_prestige): self
    {
        $this->c_prestige = $c_prestige;

        return $this;
    }

    public function getSFamilliale(): ?int
    {
        return $this->s_familliale;
    }

    public function setSFamilliale(int $s_familliale): self
    {
        $this->s_familliale = $s_familliale;

        return $this;
    }

    public function getCDeluxe(): ?int
    {
        return $this->c_deluxe;
    }

    public function setCDeluxe(int $c_deluxe): self
    {
        $this->c_deluxe = $c_deluxe;

        return $this;
    }

    public function getSVip(): ?int
    {
        return $this->s_vip;
    }

    public function setSVip(int $s_vip): self
    {
        $this->s_vip = $s_vip;

        return $this;
    }

    public function getLeNautile(): ?int
    {
        return $this->le_nautile;
    }

    public function setLeNautile(int $le_nautile): self
    {
        $this->le_nautile = $le_nautile;

        return $this;
    }

    public function getSunsetView(): ?int
    {
        return $this->sunset_view;
    }

    public function setSunsetView(int $sunset_view): self
    {
        $this->sunset_view = $sunset_view;

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
}
