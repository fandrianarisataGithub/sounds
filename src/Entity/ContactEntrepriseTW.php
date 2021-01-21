<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ContactEntrepriseTWRepository;

/**
 * @ORM\Entity(repositoryClass=ContactEntrepriseTWRepository::class)
 */
class ContactEntrepriseTW
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    private $nom_en_contact;

    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    private $telephone;

    /**
     * @ORM\ManyToOne(targetEntity=EntrepriseTW::class, inversedBy="contactEntrepriseTWs")
     */
    private $entreprise;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomEnContact(): ?string
    {
        return $this->nom_en_contact;
    }

    public function setNomEnContact(?string $nom_en_contact): self
    {
        $this->nom_en_contact = $nom_en_contact;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEntreprise(): ?EntrepriseTW
    {
        return $this->entreprise;
    }

    public function setEntreprise(?EntrepriseTW $entreprise): self
    {
        $this->entreprise = $entreprise;

        return $this;
    }
}
