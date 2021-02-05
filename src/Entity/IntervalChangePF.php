<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\IntervalChangePFRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=IntervalChangePFRepository::class)
 */
class IntervalChangePF
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_last;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_next;

    /**
     * @ORM\ManyToMany(targetEntity=ClientUpdated::class, mappedBy="intervalchangePF")
     */
    private $clientUpdateds;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $intervalle;

    public function __construct()
    {
        $this->clientUpdateds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateLast(): ?\DateTimeInterface
    {
        return $this->date_last;
    }

    public function setDateLast(?\DateTimeInterface $date_last): self
    {
        $this->date_last = $date_last;

        return $this;
    }

    public function getDateNext(): ?\DateTimeInterface
    {
        return $this->date_next;
    }

    public function setDateNext(?\DateTimeInterface $date_next): self
    {
        $this->date_next = $date_next;

        return $this;
    }

    /**
     * @return Collection|ClientUpdated[]
     */
    public function getClientUpdateds(): Collection
    {
        return $this->clientUpdateds;
    }

    public function addClientUpdated(ClientUpdated $clientUpdated): self
    {
        if (!$this->clientUpdateds->contains($clientUpdated)) {
            $this->clientUpdateds[] = $clientUpdated;
            $clientUpdated->addIntervalchangePF($this);
        }

        return $this;
    }

    public function removeClientUpdated(ClientUpdated $clientUpdated): self
    {
        if ($this->clientUpdateds->removeElement($clientUpdated)) {
            $clientUpdated->removeIntervalchangePF($this);
        }

        return $this;
    }

    public function getIntervalle(): ?string
    {
        return $this->intervalle;
    }

    public function setIntervalle(string $intervalle): self
    {
        $this->intervalle = $intervalle;

        return $this;
    }
}
