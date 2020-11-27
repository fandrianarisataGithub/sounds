<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DataTropicalWoodRepository;

/**
 * @ORM\Entity(repositoryClass=DataTropicalWoodRepository::class)
 */
class DataTropicalWood
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
    private $entreprise;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $detail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type_transaction;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $etat_production;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $paiement;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0, nullable=true)
     */
    private $montant_total;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_confirmation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $devis;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0, nullable=true)
     */
    private $montant_paye; // total reglé

    private $reste;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $idPro;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0, nullable=true)
     */
    private $total_reglement;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setReste($reste)
    {
        $this->reste = $reste;
    }

    public function getReste()
    {
        return $this->reste;
    }

    public function getEntreprise(): ?string
    {
        return $this->entreprise;
    }

    public function setEntreprise(string $entreprise): self
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function getTypeTransaction(): ?string
    {
        return $this->type_transaction;
    }

    public function setTypeTransaction(?string $type_transaction): self
    {
        $this->type_transaction = $type_transaction;

        return $this;
    }

    public function getEtatProduction(): ?string
    {
        return $this->etat_production;
    }

    public function setEtatProduction(?string $etat_production): self
    {
        $this->etat_production = $etat_production;

        return $this;
    }

    public function getPaiement(): ?string
    {
        return $this->paiement;
    }

    public function setPaiement(): self
    {
        $m = $this->montant_paye;
        $mt = $this->montant_total;
        if($m == ""){
            $this->paiement = "Aucun paiement";
        }
        
        else if($m < $mt){
            $this->paiement = "Paimenet partiel effectué";
        }
        else if($m == $mt){
            $this->paiement = "Paimenet total effectué";
        }
       
        return $this;
    }

    public function getMontantTotal(): ?string
    {
        return $this->montant_total;
    }

    public function setMontantTotal(?string $montant_total): self
    {
        $this->montant_total = $montant_total;

        return $this;
    }

    public function getDateConfirmation(): ?\DateTimeInterface
    {
        return $this->date_confirmation;
    }

    public function setDateConfirmation(?\DateTimeInterface $date_confirmation): self
    {
        $this->date_confirmation = $date_confirmation;

        return $this;
    }

    public function getDevis(): ?string
    {
        return $this->devis;
    }

    public function setDevis(?string $devis): self
    {
        $this->devis = $devis;

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

    public function getIdPro(): ?string
    {
        return $this->idPro;
    }

    public function setIdPro(?string $idPro): self
    {
        $this->idPro = $idPro;

        return $this;
    }

    public function getTotalReglement(): ?string
    {
        return $this->total_reglement;
    }

    public function setTotalReglement(?string $total_reglement): self
    {
        $this->total_reglement = $total_reglement;

        return $this;
    }
}
