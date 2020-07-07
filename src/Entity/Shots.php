<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;


/**
 * Shots
 *
 * @ORM\Table(name="shots", indexes={@ORM\Index(name="game_id", columns={"game_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\ShotsRepository")
 */
class Shots
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="player", type="integer", nullable=false)
     */
    private $player;

    /**
     * @var string
     *
     * @ORM\Column(name="attack", type="string", length=3, nullable=false)
     */
    private $attack;

    /**
     * @var string
     *
     * @ORM\Column(name="response", type="string", length=10, nullable=true)
     */
    private $response;

    /**
     * @var Game
     *
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="shots")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id")
     */
    private $game;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getPlayer(): int
    {
        return $this->player;
    }

    /**
     * @param int $player
     */
    public function setPlayer(int $player): void
    {
        $this->player = $player;
    }

    /**
     * @return string
     */
    public function getAttack(): string
    {
        return $this->attack;
    }

    /**
     * @param string $attack
     */
    public function setAttack(string $attack): void
    {
        $this->attack = $attack;
    }

    /**
     * @return string
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     * @param string $response
     */
    public function setResponse(?string $response): void
    {
        $this->response = $response;
    }
    /**
     * @return Game
     */
    public function getGame(): Game
    {
        return $this->game;
    }

    /**
     * @param Game $game
     */
    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

}
