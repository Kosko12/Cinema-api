<?php

namespace App\Tests\Unit\Service;

use App\Entity\Room;
use App\Entity\Seat;
use App\Repository\RoomRepository;
use App\Service\RoomService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class RoomServiceTest extends TestCase
{
    private RoomService $roomService;
    private RoomRepository $roomRepository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->roomRepository = $this->createMock(RoomRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->roomService = new RoomService($this->roomRepository, $this->entityManager);
    }

    public function testGenerateSeatsForRoom(): void
    {
        
        $room = new Room();
        $room->setName('Test Room');
        $room->setRows(3);
        $room->setSeatsPerRow(4);

        
        $this->roomService->generateSeatsForRoom($room);

        
        $seats = $room->getSeats();
        $this->assertCount(12, $seats, 'Should generate 12 seats (3 rows * 4 seats)');

        $seatPositions = [];
        foreach ($seats as $seat) {
            $key = $seat->getRowNumber() . '-' . $seat->getSeatNumber();
            $seatPositions[$key] = $seat;
            $this->assertFalse($seat->isReserved(), 'New seats should not be reserved');
        }

        $this->assertCount(12, $seatPositions, 'All seat positions should be unique');
        
        $this->assertArrayHasKey('1-1', $seatPositions);
        $this->assertArrayHasKey('3-4', $seatPositions);
        
        foreach ($seats as $seat) {
            $this->assertGreaterThanOrEqual(1, $seat->getRowNumber());
            $this->assertLessThanOrEqual(3, $seat->getRowNumber());
            $this->assertGreaterThanOrEqual(1, $seat->getSeatNumber());
            $this->assertLessThanOrEqual(4, $seat->getSeatNumber());
        }
    }

    public function testGenerateSeatsForRoomWithDifferentDimensions(): void
    {
        
        $room = new Room();
        $room->setName('Large Room');
        $room->setRows(5);
        $room->setSeatsPerRow(10);

        
        $this->roomService->generateSeatsForRoom($room);

        
        $seats = $room->getSeats();
        $this->assertCount(50, $seats, 'Should generate 50 seats (5 rows * 10 seats)');
    }

    public function testCalculateAvailableSeatsAllAvailable(): void
    {
        
        $room = new Room();
        $room->setName('Test Room');
        $room->setRows(2);
        $room->setSeatsPerRow(3);
        $this->roomService->generateSeatsForRoom($room);

        
        $availableSeats = $this->roomService->calculateAvailableSeats($room);

        
        $this->assertEquals(6, $availableSeats, 'All seats should be available');
    }

    public function testCalculateAvailableSeatsSomeReserved(): void
    {
        
        $room = new Room();
        $room->setName('Test Room');
        $room->setRows(2);
        $room->setSeatsPerRow(3);
        $this->roomService->generateSeatsForRoom($room);

        
        $seats = $room->getSeats()->toArray();
        $seats[0]->setIsReserved(true);
        $seats[1]->setIsReserved(true);

        
        $availableSeats = $this->roomService->calculateAvailableSeats($room);

        
        $this->assertEquals(4, $availableSeats, 'Should have 4 available seats (6 total - 2 reserved)');
    }

    public function testCalculateAvailableSeatsAllReserved(): void
    {
        
        $room = new Room();
        $room->setName('Test Room');
        $room->setRows(2);
        $room->setSeatsPerRow(2);
        $this->roomService->generateSeatsForRoom($room);

        
        foreach ($room->getSeats() as $seat) {
            $seat->setIsReserved(true);
        }

        
        $availableSeats = $this->roomService->calculateAvailableSeats($room);

        
        $this->assertEquals(0, $availableSeats, 'No seats should be available');
    }

    public function testCreateRoom(): void
    {
        
        $this->roomRepository->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function (Room $room) {
                    return $room->getName() === 'Cinema 1'
                        && $room->getRows() === 5
                        && $room->getSeatsPerRow() === 8
                        && $room->getSeats()->count() === 40;
                }),
                true
            );

        $room = $this->roomService->createRoom('Cinema 1', 5, 8);

        $this->assertEquals('Cinema 1', $room->getName());
        $this->assertEquals(5, $room->getRows());
        $this->assertEquals(8, $room->getSeatsPerRow());
        $this->assertCount(40, $room->getSeats());
    }
}
