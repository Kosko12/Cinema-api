<?php

namespace App\DTO;

class RoomResponse
{
    public int $id;
    public string $name;
    public int $rows;
    public int $seatsPerRow;
    public int $totalSeats;
    public int $availableSeats;
    public array $seats;

    public function __construct(
        int $id,
        string $name,
        int $rows,
        int $seatsPerRow,
        int $totalSeats,
        int $availableSeats,
        array $seats
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->rows = $rows;
        $this->seatsPerRow = $seatsPerRow;
        $this->totalSeats = $totalSeats;
        $this->availableSeats = $availableSeats;
        $this->seats = array_values($seats);
    }
}
