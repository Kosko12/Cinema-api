<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[ORM\Table(name: 'rooms')]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 100)]
    private string $name;

    #[ORM\Column(type: 'integer', name: '`rows`')]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private int $rows;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private int $seatsPerRow;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: Seat::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $seats;

    public function __construct()
    {
        $this->seats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getRows(): int
    {
        return $this->rows;
    }

    public function setRows(int $rows): self
    {
        $this->rows = $rows;
        return $this;
    }

    public function getSeatsPerRow(): int
    {
        return $this->seatsPerRow;
    }

    public function setSeatsPerRow(int $seatsPerRow): self
    {
        $this->seatsPerRow = $seatsPerRow;
        return $this;
    }

    public function getSeats(): Collection
    {
        return $this->seats;
    }

    public function addSeat(Seat $seat): self
    {
        if (!$this->seats->contains($seat)) {
            $this->seats[] = $seat;
            $seat->setRoom($this);
        }
        return $this;
    }

    public function removeSeat(Seat $seat): self
    {
        if ($this->seats->removeElement($seat)) {
            if ($seat->getRoom() === $this) {
                $seat->setRoom(null);
            }
        }
        return $this;
    }

    public function getTotalSeats(): int
    {
        return $this->rows * $this->seatsPerRow;
    }
}
