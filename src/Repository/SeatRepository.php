<?php

namespace App\Repository;

use App\Entity\Seat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SeatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seat::class);
    }

    public function findByRoomAndPosition(int $roomId, int $rowNumber, int $seatNumber): ?Seat
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.room = :roomId')
            ->andWhere('s.rowNumber = :rowNumber')
            ->andWhere('s.seatNumber = :seatNumber')
            ->setParameter('roomId', $roomId)
            ->setParameter('rowNumber', $rowNumber)
            ->setParameter('seatNumber', $seatNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Seat $seat, bool $flush = false): void
    {
        $this->getEntityManager()->persist($seat);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
