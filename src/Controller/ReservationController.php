<?php

namespace App\Controller;

use App\Service\ReservationService;
use App\DTO\ReservationRequest;
use App\DTO\ReservationResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/reservations')]
class ReservationController extends AbstractController
{
    public function __construct(
        private ReservationService $reservationService,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('', name: 'create_reservation', methods: ['POST'])]
    public function createReservation(Request $request): JsonResponse
    {
        try {
            $reservationRequest = $this->serializer->deserialize($request->getContent(), ReservationRequest::class, 'json');
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        $errors = $this->validator->validate($reservationRequest);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $reservation = $this->reservationService->reserveSeat(
                $reservationRequest->roomId,
                $reservationRequest->row,
                $reservationRequest->seat,
                $reservationRequest->customerEmail
            );

            $responseDto = new ReservationResponse(
                $reservation->getId(),
                $reservation->getSeat()->getRoom()->getId(),
                $reservation->getSeat()->getRowNumber(),
                $reservation->getSeat()->getSeatNumber(),
                $reservation->getCustomerEmail(),
                $reservation->getCreatedAt()->format('Y-m-d H:i:s')
            );

            return $this->json($responseDto, Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
