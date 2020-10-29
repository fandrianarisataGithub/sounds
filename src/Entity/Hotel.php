<?php

namespace App\Entity;

use App\Repository\HotelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HotelRepository::class)
 */
class Hotel
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
     * @ORM\Column(type="string", length=255)
     */
    private $lieu;

    /**
     * @ORM\OneToMany(targetEntity=Client::class, mappedBy="hotel")
     */
    private $clients;

    /**
     * @ORM\OneToMany(targetEntity=DonneeDuJour::class, mappedBy="hotel")
     */
    private $donneeDuJours;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pseudo;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="hotels")
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity=Fournisseur::class, mappedBy="hotel")
     */
    private $fournisseurs;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->donneeDuJours = new ArrayCollection();
        $this->user = new ArrayCollection();
        $this->fournisseurs = new ArrayCollection();
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

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }

    /**
     * @return Collection|Client[]
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
            $client->setHotel($this);
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->clients->contains($client)) {
            $this->clients->removeElement($client);
            // set the owning side to null (unless already changed)
            if ($client->getHotel() === $this) {
                $client->setHotel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|DonneeDuJour[]
     */
    public function getDonneeDuJours(): Collection
    {
        return $this->donneeDuJours;
    }

    public function addDonneeDuJour(DonneeDuJour $donneeDuJour): self
    {
        if (!$this->donneeDuJours->contains($donneeDuJour)) {
            $this->donneeDuJours[] = $donneeDuJour;
            $donneeDuJour->setHotel($this);
        }

        return $this;
    }

    public function removeDonneeDuJour(DonneeDuJour $donneeDuJour): self
    {
        if ($this->donneeDuJours->contains($donneeDuJour)) {
            $this->donneeDuJours->removeElement($donneeDuJour);
            // set the owning side to null (unless already changed)
            if ($donneeDuJour->getHotel() === $this) {
                $donneeDuJour->setHotel(null);
            }
        }

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->user->contains($user)) {
            $this->user->removeElement($user);
        }

        return $this;
    }

    /**
     * @return Collection|Fournisseur[]
     */
    public function getFournisseurs(): Collection
    {
        return $this->fournisseurs;
    }

    public function addFournisseur(Fournisseur $fournisseur): self
    {
        if (!$this->fournisseurs->contains($fournisseur)) {
            $this->fournisseurs[] = $fournisseur;
            $fournisseur->addHotel($this);
        }

        return $this;
    }

    public function removeFournisseur(Fournisseur $fournisseur): self
    {
        if ($this->fournisseurs->contains($fournisseur)) {
            $this->fournisseurs->removeElement($fournisseur);
            $fournisseur->removeHotel($this);
        }

        return $this;
    }
}
