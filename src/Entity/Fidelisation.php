<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\FidelisationRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=FidelisationRepository::class)
 */
class Fidelisation
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
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=Client::class, mappedBy="fidelisation")
     */
    private $client;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $limite_nuite;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $limite_ca;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icone_carte;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icone_client;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $style_etiquette;


    public function __construct()
    {
        $this->client = new ArrayCollection();
    }

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

    /**
     * @return Collection|Client[]
     */
    public function getClient(): Collection
    {
        return $this->client;
    }

    public function addClient(Client $client): self
    {
        if (!$this->client->contains($client)) {
            $this->client[] = $client;
            $client->setFidelisation($this);
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->client->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getFidelisation() === $this) {
                $client->setFidelisation(null);
            }
        }

        return $this;
    }

    public function getLimiteNuite(): ?int
    {
        return $this->limite_nuite;
    }

    public function setLimiteNuite(?int $limite_nuite): self
    {
        $this->limite_nuite = $limite_nuite;

        return $this;
    }

    public function getLimiteCa(): ?int
    {
        return $this->limite_ca;
    }

    public function setLimiteCa(?int $limite_ca): self
    {
        $this->limite_ca = $limite_ca;

        return $this;
    }

    public function getIconeCarte(): ?string
    {
        return $this->icone_carte;
    }

    public function setIconeCarte(?string $icone_carte): self
    {
        $this->icone_carte = $icone_carte;

        return $this;
    }

    public function getIconeClient(): ?string
    {
        return $this->icone_client;
    }

    public function setIconeClient(?string $icone_client): self
    {
        $this->icone_client = $icone_client;

        return $this;
    }

    public function getStyleEtiquette(): ?string
    {
        return $this->style_etiquette;
    }

    public function setStyleEtiquette(?string $style_etiquette): self
    {
        $this->style_etiquette = $style_etiquette;

        return $this;
    }

}
