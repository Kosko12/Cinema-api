<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ReservationRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $roomId;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $row;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $seat;

    #[Assert\NotBlank]
    #[Assert\Email]
    public string $customerEmail;
}
