<?php

namespace App\Entity;

use App\Entity\Game;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;


/**
 * Shots
 *
 * @ORM\Table(name="ships", indexes={@ORM\Index(name="game_id", columns={"game_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\ShipsRepository")
 */
class Ships
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
     * @ORM\Column(name="ship_type", type="integer", nullable=false)
     */
    private $shipType;

    /**
     * @var string
     *
     * @ORM\Column(name="coord", type="string", length=3, nullable=false)
     */
    private $coord;


    /**
     * @var Game
     *
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="ships")
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
    public function getShipType(): int
    {
        return $this->shipType;
    }

    /**
     * @param int $shipType
     */
    public function setShipType(int $shipType): void
    {
        $this->shipType = $shipType;
    }

    /**
     * @return string
     */
    public function getCoord(): string
    {
        return $this->coord;
    }

    /**
     * @param string $coord
     */
    public function setCoord(string $coord): void
    {
        $this->coord = $coord;
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