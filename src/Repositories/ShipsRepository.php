<?php
namespace App\Repository;

use App\Entity\Ships;
use Doctrine\ORM\EntityRepository;

class ShipsRepository extends EntityRepository
{
    public function saveShip(Ships $ship) {
        $this->getEntityManager()->persist($ship);
        $this->getEntityManager()->flush();
    }

}