<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AgentRepository")
 */
class Agent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $nickname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $realName;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    private $lat;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     */
    private $lon;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Faction")
     * @ORM\JoinColumn(nullable=false)
     */
    private $faction;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="agent")
     */
    private $comments;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $recursions;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $custom_medals;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MapGroup", inversedBy="agents")
     */
    private $map_group;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telegram_name;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function setRealName(?string $real_name): self
    {
        $this->realName = $real_name;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(float $lon): self
    {
        $this->lon = $lon;

        return $this;
    }

    public function getFaction(): ?Faction
    {
        return $this->faction;
    }

    public function setFaction(?Faction $faction): self
    {
        $this->faction = $faction;

        return $this;
    }

    public function __toString()
    {
        return $this->nickname;
    }

    public function __sleep()
    {
        return ['id', 'nickname'];
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAgent($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAgent() === $this) {
                $comment->setAgent(null);
            }
        }

        return $this;
    }

    public function getRecursions(): ?int
    {
        return $this->recursions;
    }

    public function setRecursions(?int $recursions): self
    {
        $this->recursions = $recursions;

        return $this;
    }

    public function getCustomMedals(): ?string
    {
        return $this->custom_medals;
    }

    public function setCustomMedals(?string $custom_medals): self
    {
        $this->custom_medals = $custom_medals;

        return $this;
    }

    public function getMapGroup(): ?MapGroup
    {
        return $this->map_group;
    }

    public function setMapGroup(?MapGroup $map_group): self
    {
        $this->map_group = $map_group;

        return $this;
    }

    public function getTelegramName(): ?string
    {
        return $this->telegram_name;
    }

    public function setTelegramName(?string $telegram_name): self
    {
        $this->telegram_name = $telegram_name;

        return $this;
    }
}
