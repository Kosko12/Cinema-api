<?php

namespace App\DTO;

class ReservationResponse
{
    public int $id;
    public int $roomId;
    public int $row;
    public int $seat;
    public string $customerEmail;
    public string $createdAt;

    public function __construct(
        int $id,
        int $roomId,
        int $row,
        int $seat,
        string $customerEmail,
        string $createdAt
    ) {
        $this->id = $id;
        $this->roomId = $roomId;
        $this->row = $row;
        $this->seat = $seat;
        $this->customerEmail = $customerEmail;
        $this->createdAt = $createdAt;
    }
}
