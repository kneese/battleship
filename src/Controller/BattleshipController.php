<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Shots;
use App\Entity\Ships;
use App\Repository\GameRepository;
use App\Repository\ShipsRepository;
use App\Repository\ShotsRepository;
use Exception;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BattleshipController extends AbstractFOSRestController
{
    public static $shipTypes = [
        1 => 'Carrier',
        2 => 'Battleship',
        3 => 'Cruiser',
        4 => 'Submarine',
        5 => 'Destroyer',
    ];
    public static $shipLength = [
        1 => 5,
        2 => 4,
        3 => 3,
        4 => 3,
        5 => 2,
    ];
    public static $vCoords = [
        1 => 'A',
        2 => 'B',
        3 => 'C',
        4 => 'D',
        5 => 'E',
        6 => 'F',
        7 => 'G',
        8 => 'H',
        9 => 'I',
        10 => 'J'
    ];

    /**
     * @Route("/api/battle/create", name="createBattle", methods="GET")
     */
    public function createAction(): Response
    {
        $game = new Game();
        $gameRepository = $this->getDoctrine()
            ->getRepository(Game::class);
        $game->setShootingDir(BattleshipController::getRandomShootingDirection());
        $gameRepository->saveGame($game);
        $gameRepository->loadGame($game->getId());
        $this->setShips($game);

        $responseBody = [
            'gameId' => $game->getId(),
            'response' => [
                'attack' => '',
                'message' => "It's your turn!"
                ]
            ];
        return new JsonResponse($responseBody);
    }


    /**
     * @Route("/api/battle/fight", name="fightBattle", methods="POST")
     */
    public function fightAction(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $shotsRepository = $this->getDoctrine()
            ->getRepository(Shots::class);
        $gameRepository = $this->getDoctrine()
            ->getRepository(Game::class);
        $game = $gameRepository->loadGame($data['gameId']);
        $gameRepository->refreshGame($game);
        if (!($game instanceof Game) || $data['gameId'] != $game->getId()){
            return new JsonResponse(['message' => 'Game not foundt!']);
        }
        //Check attack
        if (false === $this->checkCoord($data['attack'])) {
            return new JsonResponse(['Error' => "Attack-coords have to be between A1 an J10."]);
        }
        $shots[1] = $game->getShotsByPLayer(1);
        $shots[2] = $game->getShotsByPLayer(2);
        // Check if response is needed
        if (0 < count($shots[2]) && '' == $shots[2][count($shots[2]) - 1]->getResponse()) {
            $lastShot = $shots[2][count($shots[2]) - 1];
            $responseCheck = $this->checkResponse($data['response'], $lastShot);
            if (true != $responseCheck) {
                return $responseCheck;
            }
            $setResponse = $data['response']['message'];
            if (isset($data['response']['shipType'])) {
                $setResponse .= ';' . array_search($data['response']['shipType'], BattleshipController::$shipTypes);
            }
            if (('hit' == $data['response']['message'] || 'sunk' == $data['response']['message'])
                && 17 == $shotsRepository->getCountOfHits($game, 2)) {
                //Game Over - Computer wins!
                return new JsonResponse(['response' => ['message' => 'Game over! Computer wins!']], 200);
            }

            $lastShot->setResponse($setResponse);
            $shotsRepository->saveShot($lastShot);
            $gameRepository->refreshGame($game);
        }
        //check if shot already shot?
        if ($shotsRepository->checkShotNotShot($data['attack'], 1, $game)) {
            $shot = $shotsRepository->addShot($data['attack'], 1, $game);
            $gameRepository->refreshGame($game);
            $shotResponse = $shotsRepository->getShotResponse($shot, $game, $gameRepository, $shotsRepository);
            if ('sunk' == $shotResponse['message']) {
                if (17 == $shotsRepository->getCountOfHits($game, 1)) {
                    //Game Over
                    $shotResponse['message'] .= ' - Game over! Player wins!';
                }
            }
        } else {
            return new JsonResponse(['Error' => "You already shot on ({$data['attack']})."], 200);
    }
        $attackShot = $shotsRepository->calculateShot($game, $gameRepository);
        $responseBody = [
            'gameId' => $game->getId(),
            'attack' => $attackShot->getAttack(),
            'response' => $shotResponse,
            //'battleMode' => $data['battleMode'],
            ];
        return new JsonResponse($responseBody);
    }

    /**
     * @Route("/api/battle", name="battle", methods="GET")
     */
    public function indexAction(): Response
    {
        return new JsonResponse([
            'battle' => 'The first Step for a fight'
        ]);
    }

    private function checkCoord($coord)
    {
        $v = substr($coord, 0, 1);
        if ($v < 'A' || $v > 'J') {
            return false;
        }
        $h = intval(substr($coord, 1));
        if ($h < 1 || $h > 10) {
            return false;
        }
        return true;
    }

    private function checkResponse($response, Shots $lastShot)
    {
        if ($response['attack'] != $lastShot->getAttack()) {
            return new JsonResponse(['Error' => "Wrong attck-coords ({$response['attack']}) in response. It has to be ({$lastShot->getAttack()})!"]);
        }
        $message = strtolower($response['message']);
        if ('sunk' == $message || 'hit' == $message) {
            if (false === array_search($response['shipType'], BattleshipController::$shipTypes)) {
                return new JsonResponse(['Error' => "Unknown shiptype ({$response['shipType']}) in response. Allowed values: Carrier, Battleship, Cruiser, Submarine and Destroyer"]);
            }
        } elseif ('miss' != $message) {
            return new JsonResponse(['Error' => "Unknown or empty response ({$response['message']}). Allowed values: 'hit', 'sunk', 'miss'"]);
        }
        return true;
    }

    protected function setShips(Game $game)
    {
        $this->setShip(1, $game);
        $this->setShip(2, $game);
        $this->setShip(3, $game);
        $this->setShip(4, $game);
        $this->setShip(5, $game);
    }

    protected function setShip($shipType, Game $game)
    {
        $gameRepository = $this->getDoctrine()
            ->getRepository(Game::class);
        $gameRepository->refreshGame($game);


        $ships = $game->getShips();

        $shipCoords = [];
        if(is_countable($ships)) {
            foreach ($ships as $ship) {
                $shipCoords[] = trim($ship->getCoord());
            }
        }

        switch($shipType){
            case 1:
                $shipLength = 5;
                break;
            case 2:
                $shipLength = 4;
                break;
            case 3:
            case 4:
                $shipLength = 3;
                break;
            case 5:
                $shipLength = 2;
                break;
            default:
                throw new Exception("Illeagal shiptype: '{$shipType}' ", 500);
        }
        $direction = mt_rand(1,2);
        $vMax = 10;
        $hMax = 10;
        if (1 == $direction) {
            //placing ship horizontal
            $hMax = 10 - ($shipLength - 1);
        } else {
            //placing ship vertical
            $vMax = 10 - ($shipLength - 1);
        }
        $sCoords = [];
        $hCoord = 0;
        $vCoord = 0;
        $debug = '';
        while (count($sCoords) < $shipLength) {
            if (0 == count($sCoords)) {
                $hCoord = mt_rand(1, $hMax);
                $vCoord = mt_rand(1, $vMax);
            } else {
                if (1 == $direction) {
                    $hCoord ++;
                } else {
                    $vCoord ++;
                }
            }
            //throw new \Exception($vCoords[$vCoord].$hCoord, 200);
            $newCoord = BattleshipController::$vCoords[$vCoord] . $hCoord;
            $debug .= ',' . $newCoord;
            if (!in_array($newCoord, $shipCoords)) {
                $sCoords[] = $newCoord;
                $shipCoords[] = $newCoord;
            } else {
                $sCoords = [];
                $debug .= ', DEL';
            }
        }
        foreach ($sCoords as $sCoord) {
            $ship = new Ships();
            $ship->setGame($game);
            $ship->setShipType($shipType);
            $ship->setCoord($sCoord);
            $shipRepository = $this->getDoctrine()
                ->getRepository(Ships::class);
            $shipRepository->saveShip($ship);
        }
    }

    protected static function getRandomShootingDirection()
    {
        return mt_rand(0,3) * 90;
    }

}
