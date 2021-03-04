<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\TresorerieRecetteRepository;

/**
 * @ORM\Entity(repositoryClass=TresorerieRecetteRepository::class)
 */
class TresorerieRecette extends Tresorerie
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
    private $id_pro;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom_client;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdPro(): ?string
    {
        return $this->id_pro;
    }

    public function setIdPro(?string $id_pro): self
    {
        $this->id_pro = $id_pro;

        return $this;
    }

    public function getNomClient(): ?string
    {
        return $this->nom_client;
    }

    public function setNomClient(?string $nom_client): self
    {
        $this->nom_client = $nom_client;
        return $this;
    }
}
