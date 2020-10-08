<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DonneeDuJourRepository;

/**
 * @ORM\Entity(repositoryClass=DonneeDuJourRepository::class)
 */
class DonneeDuJour
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $heb_to;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $heb_ca;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $res_n_couvert;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $res_ca;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $res_p_dej;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $res_dej;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $res_dinner;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $spa_ca;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $spa_n_abonne;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $spa_c_unique;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $crj_direction;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $crj_service_rh;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $crj_commercial;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $crj_comptable;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $crj_reception;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $crj_restaurant;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $crj_spa;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $crj_s_technique;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $crj_litiges;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Hotel::class, inversedBy="donneeDuJours")
     * @ORM\JoinColumn(nullable=false)
     */
    private $hotel;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHebTo(): ?string
    {
        return $this->heb_to;
    }

    public function setHebTo(string $heb_to): self
    {
        $this->heb_to = $heb_to;

        return $this;
    }

    public function getHebCa(): ?string
    {
        return $this->heb_ca;
    }

    public function setHebCa(string $heb_ca): self
    {
        $this->heb_ca = $heb_ca;

        return $this;
    }

    public function getResNCouvert(): ?string
    {
        return $this->res_n_couvert;
    }

    public function setResNCouvert(string $res_n_couvert): self
    {
        $this->res_n_couvert = $res_n_couvert;

        return $this;
    }

    public function getResCa(): ?string
    {
        return $this->res_ca;
    }

    public function setResCa(string $res_ca): self
    {
        $this->res_ca = $res_ca;

        return $this;
    }

    public function getResPDej(): ?string
    {
        return $this->res_p_dej;
    }

    public function setResPDej(string $res_p_dej): self
    {
        $this->res_p_dej = $res_p_dej;

        return $this;
    }

    public function getResDej(): ?string
    {
        return $this->res_dej;
    }

    public function setResDej(string $res_dej): self
    {
        $this->res_dej = $res_dej;

        return $this;
    }

    public function getResDinner(): ?string
    {
        return $this->res_dinner;
    }

    public function setResDinner(string $res_dinner): self
    {
        $this->res_dinner = $res_dinner;

        return $this;
    }

    public function getSpaCa(): ?string
    {
        return $this->spa_ca;
    }

    public function setSpaCa(string $spa_ca): self
    {
        $this->spa_ca = $spa_ca;

        return $this;
    }

    public function getSpaNAbonne(): ?string
    {
        return $this->spa_n_abonne;
    }

    public function setSpaNAbonne(string $spa_n_abonne): self
    {
        $this->spa_n_abonne = $spa_n_abonne;

        return $this;
    }

    public function getSpaCUnique(): ?string
    {
        return $this->spa_c_unique;
    }

    public function setSpaCUnique(string $spa_c_unique): self
    {
        $this->spa_c_unique = $spa_c_unique;

        return $this;
    }

    public function getCrjDirection(): ?string
    {
        return $this->crj_direction;
    }

    public function setCrjDirection(?string $crj_direction): self
    {
        $this->crj_direction = $crj_direction;

        return $this;
    }

    public function getCrjServiceRh(): ?string
    {
        return $this->crj_service_rh;
    }

    public function setCrjServiceRh(?string $crj_service_rh): self
    {
        $this->crj_service_rh = $crj_service_rh;

        return $this;
    }

    public function getCrjCommercial(): ?string
    {
        return $this->crj_commercial;
    }

    public function setCrjCommercial(?string $crj_commercial): self
    {
        $this->crj_commercial = $crj_commercial;

        return $this;
    }

    public function getCrjComptable(): ?string
    {
        return $this->crj_comptable;
    }

    public function setCrjComptable(?string $crj_comptable): self
    {
        $this->crj_comptable = $crj_comptable;

        return $this;
    }

    public function getCrjReception(): ?string
    {
        return $this->crj_reception;
    }

    public function setCrjReception(?string $crj_reception): self
    {
        $this->crj_reception = $crj_reception;

        return $this;
    }

    public function getCrjRestaurant(): ?string
    {
        return $this->crj_restaurant;
    }

    public function setCrjRestaurant(?string $crj_restaurant): self
    {
        $this->crj_restaurant = $crj_restaurant;

        return $this;
    }

    public function getCrjSpa(): ?string
    {
        return $this->crj_spa;
    }

    public function setCrjSpa(?string $crj_spa): self
    {
        $this->crj_spa = $crj_spa;

        return $this;
    }

    public function getCrjSTechnique(): ?string
    {
        return $this->crj_s_technique;
    }

    public function setCrjSTechnique(?string $crj_s_technique): self
    {
        $this->crj_s_technique = $crj_s_technique;

        return $this;
    }

    public function getCrjLitiges(): ?string
    {
        return $this->crj_litiges;
    }

    public function setCrjLitiges(?string $crj_litiges): self
    {
        $this->crj_litiges = $crj_litiges;

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
