<?php
namespace App\Repository;

use App\Entity\Game;
use Doctrine\ORM\EntityRepository;

class GameRepository extends EntityRepository
{
    public function findAllOrderedById()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT g FROM App:Game g ORDER BY g.id ASC'
            )
            ->getResult();
    }

    public function loadGame($id): Game
    {
        return $this->getEntityManager()->getRepository(Game::class)->find($id);
    }

    public function saveGame(Game $game)
    {
        $this->getEntityManager()->persist($game);
        $this->getEntityManager()->flush();
    }
    public function refreshGame(Game $game)
    {
        $this->getEntityManager()->refresh($game);
    }
    public function getCountOfShipHits(Game $game, $shipType)
    {
        $shots = $game->getShotsByPLayer(1);
        $hitCounter = 0;
        if (is_countable($shots) && 0 < count($shots)) {
            foreach ($shots as $shot) {
                if (false !== stripos($shot->getResponse(), ';')) {
                    list($shotMessage, $shotShipType) = explode(';', $shot->getResponse());
                    if ($shotShipType == $shipType) {
                        $hitCounter++;
                    }
                }
            }
        }
        return $hitCounter;
    }

}