<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateRoomRequest
{
    #[Assert\NotBlank]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $rows;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $seatsPerRow;
}
