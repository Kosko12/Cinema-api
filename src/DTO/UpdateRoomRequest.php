<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateRoomRequest
{
    #[Assert\NotBlank]
    public string $name;

    #[Assert\Positive]
    public int $rows;

    #[Assert\Positive]
    public int $seatsPerRow;
}
