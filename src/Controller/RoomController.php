<?php

namespace App\Controller;

use App\Service\RoomService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/rooms')]
class RoomController extends AbstractController
{
    public function __construct(
        private RoomService $roomService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'get_rooms', methods: ['GET'])]
    public function getRooms(): JsonResponse
    {
        $rooms = $this->roomService->getAllRooms();
        
        $data = array_map(function ($room) {
            return [
                'id' => $room->getId(),
                'name' => $room->getName(),
                'rows' => $room->getRows(),
                'seatsPerRow' => $room->getSeatsPerRow(),
                'totalSeats' => $room->getTotalSeats(),
                'availableSeats' => $this->roomService->calculateAvailableSeats($room),
                'seats' => array_map(function ($seat) {
                    return [
                        'id' => $seat->getId(),
                        'row' => $seat->getRowNumber(),
                        'number' => $seat->getSeatNumber(),
                        'isReserved' => $seat->isReserved(),
                    ];
                }, $room->getSeats()->toArray()),
            ];
        }, $rooms);

        return $this->json($data);
    }

    #[Route('', name: 'create_room', methods: ['POST'])]
    public function createRoom(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['rows']) || !isset($data['seatsPerRow'])) {
            return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $room = $this->roomService->createRoom(
                $data['name'],
                (int) $data['rows'],
                (int) $data['seatsPerRow']
            );

            return $this->json([
                'id' => $room->getId(),
                'name' => $room->getName(),
                'rows' => $room->getRows(),
                'seatsPerRow' => $room->getSeatsPerRow(),
                'totalSeats' => $room->getTotalSeats(),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'update_room', methods: ['PUT'])]
    public function updateRoom(int $id, Request $request): JsonResponse
    {
        $room = $this->roomService->getRoomById($id);

        if (!$room) {
            return $this->json(['error' => 'Room not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['rows']) || !isset($data['seatsPerRow'])) {
            return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $room = $this->roomService->updateRoom(
                $room,
                $data['name'],
                (int) $data['rows'],
                (int) $data['seatsPerRow']
            );

            return $this->json([
                'id' => $room->getId(),
                'name' => $room->getName(),
                'rows' => $room->getRows(),
                'seatsPerRow' => $room->getSeatsPerRow(),
                'totalSeats' => $room->getTotalSeats(),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'delete_room', methods: ['DELETE'])]
    public function deleteRoom(int $id): JsonResponse
    {
        $room = $this->roomService->getRoomById($id);

        if (!$room) {
            return $this->json(['error' => 'Room not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->roomService->deleteRoom($room);
            return $this->json(['message' => 'Room deleted successfully']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
