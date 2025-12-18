<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Seat;
use App\Repository\ReservationRepository;
use App\Repository\SeatRepository;

class ReservationService
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private SeatRepository $seatRepository
    ) {
    }

    public function reserveSeat(int $roomId, int $rowNumber, int $seatNumber, string $customerEmail): Reservation
    {
        $seat = $this->seatRepository->findByRoomAndPosition($roomId, $rowNumber, $seatNumber);

        if (!$seat) {
            throw new \InvalidArgumentException('Seat not found');
        }

        if ($seat->isReserved()) {
            throw new \RuntimeException('Seat is already reserved');
        }

        $seat->setIsReserved(true);
        $this->seatRepository->save($seat);

        $reservation = new Reservation();
        $reservation->setSeat($seat);
        $reservation->setCustomerEmail($customerEmail);

        $this->reservationRepository->save($reservation, true);

        return $reservation;
    }
}
