<?php

namespace App\Service;

use App\Entity\Room;
use App\Entity\Seat;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;

class RoomService
{
    public function __construct(
        private RoomRepository $roomRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function createRoom(string $name, int $rows, int $seatsPerRow): Room
    {
        $room = new Room();
        $room->setName($name);
        $room->setRows($rows);
        $room->setSeatsPerRow($seatsPerRow);

        $this->generateSeatsForRoom($room);

        $this->roomRepository->save($room, true);

        return $room;
    }

    public function updateRoom(Room $room, string $name, int $rows, int $seatsPerRow): Room
    {
        $room->setName($name);
        
        // Jeśli zmieniono wymiary sali, usuń stoliki i stwórz nowe
        if ($room->getRows() !== $rows || $room->getSeatsPerRow() !== $seatsPerRow) {
            $room->setRows($rows);
            $room->setSeatsPerRow($seatsPerRow);
            
            // Usuwanie stolików
            $oldSeats = $room->getSeats()->toArray();
            foreach ($oldSeats as $seat) {
                $this->entityManager->remove($seat);
            }
            $this->entityManager->flush();
            
            // Generowanie nowych stolików
            $this->generateSeatsForRoom($room);
        }

        $this->entityManager->flush();

        return $room;
    }

    public function deleteRoom(Room $room): void
    {
        $this->roomRepository->remove($room, true);
    }

    public function getAllRooms(): array
    {
        return $this->roomRepository->findAll();
    }

    public function getRoomById(int $id): ?Room
    {
        return $this->roomRepository->find($id);
    }


    public function generateSeatsForRoom(Room $room): void
    {
        for ($row = 1; $row <= $room->getRows(); $row++) {
            for ($seatNum = 1; $seatNum <= $room->getSeatsPerRow(); $seatNum++) {
                $seat = new Seat();
                $seat->setRowNumber($row);
                $seat->setSeatNumber($seatNum);
                $seat->setIsReserved(false);
                $room->addSeat($seat);
            }
        }
    }

    public function calculateAvailableSeats(Room $room): int
    {
        $availableCount = 0;
        foreach ($room->getSeats() as $seat) {
            if (!$seat->isReserved()) {
                $availableCount++;
            }
        }
        return $availableCount;
    }
}
