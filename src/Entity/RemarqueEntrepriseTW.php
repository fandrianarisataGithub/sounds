<?php

namespace App\Entity;

use App\Repository\RemarqueEntrepriseTWRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RemarqueEntrepriseTWRepository::class)
 */
class RemarqueEntrepriseTW
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_remarque;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $concerne;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $observation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $etat_resultat;

    /**
     * @ORM\ManyToOne(targetEntity=EntrepriseTW::class, inversedBy="remarqueEntrepriseTWs")
     */
    private $entreprise;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateRemarque(): ?\DateTimeInterface
    {
        return $this->date_remarque;
    }

    public function setDateRemarque(\DateTimeInterface $date_remarque): self
    {
        $this->date_remarque = $date_remarque;

        return $this;
    }

    public function getConcerne(): ?string
    {
        return $this->concerne;
    }

    public function setConcerne(string $concerne): self
    {
        $this->concerne = $concerne;

        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(string $observation): self
    {
        $this->observation = $observation;

        return $this;
    }

    public function getEtatResultat(): ?string
    {
        return $this->etat_resultat;
    }

    public function setEtatResultat(string $etat_resultat): self
    {
        $this->etat_resultat = $etat_resultat;

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
