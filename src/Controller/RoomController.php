<?php

namespace App\Controller;

use App\Service\RoomService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\DTO\CreateRoomRequest;
use App\DTO\RoomResponse;
use App\DTO\UpdateRoomRequest;
#[Route('/api/rooms')]
class RoomController extends AbstractController
{
    public function __construct(
        private RoomService $roomService,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('', name: 'get_rooms', methods: ['GET'])]
    public function getRooms(): JsonResponse
    {
        $rooms = $this->roomService->getAllRooms();
        
        $data = array_map(function ($room) {
            return new \App\DTO\RoomResponse(
                $room->getId(),
                $room->getName(),
                $room->getRows(),
                $room->getSeatsPerRow(),
                $room->getTotalSeats(),
                $this->roomService->calculateAvailableSeats($room),
                array_map(function ($seat) {
                    return [
                        'id' => $seat->getId(),
                        'row' => $seat->getRowNumber(),
                        'number' => $seat->getSeatNumber(),
                        'isReserved' => $seat->isReserved(),
                    ];
                }, $room->getSeats()->toArray())
            );
        }, $rooms);

        return $this->json($data);
    }

    #[Route('', name: 'create_room', methods: ['POST'])]
    public function createRoom(Request $request): JsonResponse
    {
        try {
            $roomRequest = $this->serializer->deserialize($request->getContent(), CreateRoomRequest::class, 'json');
            $errors = $this->validator->validate($roomRequest);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $room = $this->roomService->createRoom(
                $roomRequest->name,
                (int) $roomRequest->rows,
                (int) $roomRequest->seatsPerRow
            );

            $responseDto = new \App\DTO\RoomResponse(
                $room->getId(),
                $room->getName(),
                $room->getRows(),
                $room->getSeatsPerRow(),
                $room->getTotalSeats(),
                $this->roomService->calculateAvailableSeats($room),
                array_map(function ($seat) {
                    return [
                        'id' => $seat->getId(),
                        'row' => $seat->getRowNumber(),
                        'number' => $seat->getSeatNumber(),
                        'isReserved' => $seat->isReserved(),
                    ];
                }, $room->getSeats()->toArray())
            );
            return $this->json($responseDto, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'update_room', methods: ['PUT'])]
    public function updateRoom(int $id, Request $request): JsonResponse
    {
        try {
            $room = $this->roomService->getRoomById($id);

            if (!$room) {
                return $this->json(['error' => 'Room not found'], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        try {
            $roomRequest = $this->serializer->deserialize($request->getContent(), UpdateRoomRequest::class, 'json');
            $errors = $this->validator->validate($roomRequest);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $room = $this->roomService->updateRoom(
                $room,
                $roomRequest->name,
                isset($roomRequest->rows) ? (int) $roomRequest->rows : null,
                isset($roomRequest->seatsPerRow) ? (int) $roomRequest->seatsPerRow : null
            );
            
            $responseDto = new \App\DTO\RoomResponse(
                $room->getId(),
                $room->getName(),
                $room->getRows(),
                $room->getSeatsPerRow(),
                $room->getTotalSeats(),
                $this->roomService->calculateAvailableSeats($room),
                array_map(function ($seat) {
                    return [
                        'id' => $seat->getId(),
                        'row' => $seat->getRowNumber(),
                        'number' => $seat->getSeatNumber(),
                        'isReserved' => $seat->isReserved(),
                    ];
                }, $room->getSeats()->toArray())
            );
            return $this->json($responseDto);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'delete_room', methods: ['DELETE'])]
    public function deleteRoom(int $id): JsonResponse
    {
        try {
            $room = $this->roomService->getRoomById($id);

            if (!$room) {
                return $this->json(['error' => 'Room not found'], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->roomService->deleteRoom($room);
            return $this->json(['message' => 'Room deleted successfully']);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
