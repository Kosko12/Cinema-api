<?php

namespace App\Controller;

use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reservations')]
class ReservationController extends AbstractController
{
    public function __construct(
        private ReservationService $reservationService
    ) {
    }

    #[Route('', name: 'create_reservation', methods: ['POST'])]
    public function createReservation(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['roomId']) || !isset($data['row']) || !isset($data['seat']) || !isset($data['customerEmail'])) {
            return $this->json(['error' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $reservation = $this->reservationService->reserveSeat(
                (int) $data['roomId'],
                (int) $data['row'],
                (int) $data['seat'],
                $data['customerEmail']
            );

            return $this->json([
                'id' => $reservation->getId(),
                'roomId' => $reservation->getSeat()->getRoom()->getId(),
                'row' => $reservation->getSeat()->getRowNumber(),
                'seat' => $reservation->getSeat()->getSeatNumber(),
                'customerEmail' => $reservation->getCustomerEmail(),
                'createdAt' => $reservation->getCreatedAt()->format('Y-m-d H:i:s'),
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
