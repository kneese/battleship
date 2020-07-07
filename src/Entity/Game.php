<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumn;
use App\Entity\Shots;
use App\Entity\Ships;
use mysql_xdevapi\Exception;

/**
 * Game
 *
 * @ORM\Table(name="game")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 */
class Game
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
     * @ORM\Column(name="shooting_dir", type="integer", nullable=false)
     */
    private $shootingDir;

    /**
     * One Games has Many Shots.
     * @OneToMany(targetEntity="Shots", mappedBy="game")
     */
    private $shots;
    /**
     * One Games has Many Ships.
     * @OneToMany(targetEntity="Ships", mappedBy="game")
     */
    private $ships;

    public function __construct() {
        $this->shots = new ArrayCollection();
        $this->ships = new ArrayCollection();
    }

    public function getShots()
    {
        return $this->shots;
    }
    public function getShips()
    {
        return $this->ships;
    }

    public function getShotsByPLayer($player)
    {
        $playerShots = [];
        if (0 < count($this->shots)) {
            foreach ($this->shots as $shot) {
                if ($player == $shot->getPlayer()) {
                    $playerShots[] = $shot;
                }
            }
        }
        return $playerShots;
    }


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
    public function getShootingDir(): int
    {
        return $this->shootingDir;
    }

    /**
     * @param int $shootingDir
     */
    public function setShootingDir(int $shootingDir): void
    {
        $this->shootingDir = $shootingDir;
    }
    public function addShot(Shots $shot)
    {
        $this->shots[] = $shot;
    }
    public function addShip(Ships $ship)
    {
        $this->ships[] = $ship;
    }


}
