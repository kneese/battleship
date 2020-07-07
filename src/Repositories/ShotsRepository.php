<?php
namespace App\Repository;

use App\Controller\BattleshipController;
use App\Entity\Game;
use App\Entity\Shots;
use Doctrine\ORM\EntityRepository;
use function Doctrine\ORM\QueryBuilder;

class ShotsRepository extends EntityRepository
{
    public function getShotsByPlayer($playerNumber, $gameId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb
            ->select('s')
            ->from('App:Shots', 's')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('s.game', ':gameId'),
                $qb->expr()->eq('s.player', ':player')
            ))
            ->orderBy('s.id', 'ASC')
            ->setParameters(['player' => $playerNumber, 'gameId' => $gameId])
            ->getQuery();
        return $query->getResult();


        return $this->getEntityManager()
            ->createQuery(
                'SELECT s FROM App:Shots s WHERE player = :player ORDER BY g.id ASC'
            )
            ->setParameters(['player' => $playerNumber])
            ->getResult();
    }
    public function saveShot(Shots $shot) {
        $this->getEntityManager()->persist($shot);
        $this->getEntityManager()->flush();
    }

    public function addShot($coord, $player, Game $game) : Shots
    {
        $shot = new Shots();
        $shot->setAttack($coord);
        $shot->setPlayer($player);
        $shot->setGame($game);
        $this->saveShot($shot);
        return $shot;
    }

    public function checkShotNotShot($coord, $player, Game $game) : bool
    {
        $shotsByPlayer = $game->getShotsByPLayer($player);
        $debug = count($shotsByPlayer) . '#';
        foreach ($shotsByPlayer as $shotByPlayer) {
            $debug .= $shotByPlayer->getAttack() . ";";
            if($shotByPlayer->getAttack() == $coord) {
                return false;
            }
        }
        return true;
    }

    public function getShotResponse(Shots $shot, Game $game, GameRepository $gameRepository, ShotsRepository $shotsRepository)
    {
        $ships = $game->getShips();
        $response = ['attack' => $shot->getAttack(), 'message' => 'miss'];
        $shot->setResponse('miss');
        foreach ($ships as $ship) {
            if ($ship->getCoord() == $shot->getAttack()) {
                //hit -> check if sunk
                $hitCounter = $gameRepository->getCountOfShipHits($game, $ship->getShipType());
                $hit = 'hit';

                if ($hitCounter == BattleshipController::$shipLength[$ship->getShipType()] - 1) {
                    $hit = 'sunk';
                }
                $shot->setResponse($hit. ';' . $ship->getShipType());
                $response['message'] = $hit;
                $response['shipType'] = BattleshipController::$shipTypes[$ship->getShipType()];
            }
        }
        $shotsRepository->saveShot($shot);
        return $response;
    }

    public function getCountOfHits(Game $game, int $player) : int
    {
        $hitCounter = 0;
        $shotsByPlayer = $game->getShotsByPLayer($player);
        foreach ($shotsByPlayer as $shot) {
            $response = $shot->getResponse();
            if (false !== stripos($response, 'hit') || false !== stripos($response, 'sunk')) {
                $hitCounter ++;
            }
        }
        return $hitCounter;
    }

    public function calculateShot(Game $game, GameRepository $gameRepository) : Shots
    {
        $shotsReverse = array_reverse($game->getShotsByPLayer(2));
        if (0 == count($shotsReverse)) {
            return $this->getRandomShot($game);
        }
        $lastShot = $shotsReverse[0];

        //find lastHit
        $run = 0;
        foreach ($shotsReverse as $shot) {

            if (false === stripos($shot->getResponse(), ';')) {
                $history[$run] = [$shot->getResponse()];
                $message = ($shot->getResponse());
            } else {
                list($message, $shipType) = explode(';', $shot->getResponse());
            }

            if ('sunk' == $message){
                //ship is sunk, calculate randomShot
                return $this->getRandomShot($game);
            } elseif ('hit' == $message) {
                if ('miss' == $lastShot->getResponse()) {
                    // if shot $run+1 ist hit change shootingDir by 180 degree
                    $run1 = 1;
                    $debug = '';
                    //run down hit to find first in the row
                    if ('hit' == substr($shotsReverse[$run + $run1]->getResponse(),0, 3)){
                        $shootingDir = ($game->getShootingDir() + 180) % 360;
                        $shipType = substr($shotsReverse[$run + $run1]->getResponse(),4, 1);
                        $debug .= "§§".$shipType."§§";
                        $search = true;
                        while ($search) {
                            $response = $shotsReverse[$run + $run1]->getResponse();
                            if ('hit' == substr($response,0,3) &&
                            $shipType == substr($response,4, 1)) {
                                $positionToCalculate = $shotsReverse[$run + $run1]->getAttack();
                                $debug .= ";" . $positionToCalculate;
                            }
                            $run1 ++;
                            if (isset($shotsReverse[$run + $run1])) {
                                $debug .= "({$shotsReverse[$run + $run1]->getAttack()}-{$shotsReverse[$run + $run1]->getResponse()})";
                            }
                            if (!isset($shotsReverse[$run + $run1])) {
                                $search = false;
                            }
                        }
                    } else {
                        $shootingDir = ($game->getShootingDir() + 90) % 360;
                        $positionToCalculate = $shot->getAttack();
                    }
                    $game->setShootingDir($shootingDir);
                    $gameRepository->saveGame($game);
                } else {
                    $positionToCalculate = $shot->getAttack();
                }
                //throw new \Exception($debug);
                $vCoord = array_search(substr($positionToCalculate, 0, 1), BattleshipController::$vCoords);
                $hCoord = intval(substr($positionToCalculate, 1));
                // dont shoot out of bounds
                $saveGame = false;
                if(0 == $game->getShootingDir() && $vCoord == 0) {
                    $game->getShootingDir(($game->getShootingDir() + 90) % 360);
                    $saveGame = true;
                }
                if(90 == $game->getShootingDir() && $hCoord == 10) {
                    $game->getShootingDir(($game->getShootingDir() + 90) % 360);
                    $saveGame = true;
                }
                if(180 == $game->getShootingDir() && $vCoord == 10) {
                    $game->getShootingDir(($game->getShootingDir() + 90) % 360);
                    $saveGame = true;
                }
                if(270 == $game->getShootingDir() && $hCoord == 0) {
                    $game->getShootingDir(($game->getShootingDir() + 90) % 360);
                    $saveGame = true;
                }
                if(true === $saveGame) {
                    $gameRepository->saveGame($game);
                }

                switch($game->getShootingDir()) {
                    case 0:
                        $vCoord --;
                        break;
                    case 90:
                        $hCoord ++;
                        break;
                    case 180:
                        $vCoord ++;
                        break;
                    case 270:
                        $hCoord --;
                        break;
                }
                $shotCoord = BattleshipController::$vCoords[$vCoord] . $hCoord;
                if($this->checkShotNotShot($shotCoord, 2, $game)) {
                    return $this->addShot($shotCoord, 2, $game);
                } else {
                    return $this->getRandomShot($game);
                }
            }
            $run ++;
        }
        return $this->getRandomShot($game);
    }
    protected function getRandomShot(Game $game) : Shots
    {
        $shotArray = [];
        $shotCoord = '';
        do {
            if ('' != $shotCoord) {
                $shotArray[] = $shotCoord;
            }
            $hCoord = mt_rand(1, 10);
            $vCoord = mt_rand(1, 10);
            $shotCoord = BattleshipController::$vCoords[$vCoord] . $hCoord;
        } while(in_array($shotCoord, $shotArray) || false == $this->checkShotNotShot($shotCoord, 2, $game));
        return $this->addShot($shotCoord, 2, $game);
    }
}