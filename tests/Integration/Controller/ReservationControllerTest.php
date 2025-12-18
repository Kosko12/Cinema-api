<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ReservationControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        
        // Czyszczenie bazy danych przed każdym testem
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        
        $entityManager->createQuery('DELETE FROM App\Entity\Reservation')->execute();
        $entityManager->createQuery('DELETE FROM App\Entity\Seat')->execute();
        $entityManager->createQuery('DELETE FROM App\Entity\Room')->execute();
        $entityManager->clear();
    }

    public function testCreateReservationSuccessfully(): void
    {
        // Tworzenie sali z miejscami
        $this->client->request(
            'POST',
            '/api/rooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test Cinema',
                'rows' => 2,
                'seatsPerRow' => 3
            ])
        );

        $roomResponse = json_decode($this->client->getResponse()->getContent(), true);
        $roomId = $roomResponse['id'];

        // Tworzenie rezerwacji
        $this->client->request(
            'POST',
            '/api/reservations',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'roomId' => $roomId,
                'row' => 1,
                'seat' => 1,
                'customerEmail' => 'customer@example.com'
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertEquals($roomId, $content['roomId']);
        $this->assertEquals(1, $content['row']);
        $this->assertEquals(1, $content['seat']);
        $this->assertEquals('customer@example.com', $content['customerEmail']);
        $this->assertArrayHasKey('createdAt', $content);
    }

    public function testCreateReservationForAlreadyReservedSeat(): void
    {
        // Tworzenie sali
        $this->client->request(
            'POST',
            '/api/rooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test Cinema',
                'rows' => 2,
                'seatsPerRow' => 2
            ])
        );

        $roomResponse = json_decode($this->client->getResponse()->getContent(), true);
        $roomId = $roomResponse['id'];

        // Tworzenie pierwszej rezerwacji
        $this->client->request(
            'POST',
            '/api/reservations',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'roomId' => $roomId,
                'row' => 1,
                'seat' => 1,
                'customerEmail' => 'first@example.com'
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Próba zarezerwowania tego samego miejsca
        $this->client->request(
            'POST',
            '/api/reservations',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'roomId' => $roomId,
                'row' => 1,
                'seat' => 1,
                'customerEmail' => 'second@example.com'
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertStringContainsString('already reserved', $content['error']);
    }

    public function testCreateReservationWithInvalidSeat(): void
    {
        // Tworzenie sali
        $this->client->request(
            'POST',
            '/api/rooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test Cinema',
                'rows' => 2,
                'seatsPerRow' => 2
            ])
        );

        $roomResponse = json_decode($this->client->getResponse()->getContent(), true);
        $roomId = $roomResponse['id'];

        // Próba zarezerwowania nieistniejącego miejsca
        $this->client->request(
            'POST',
            '/api/reservations',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'roomId' => $roomId,
                'row' => 10,
                'seat' => 10,
                'customerEmail' => 'customer@example.com'
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $content);
    }

    public function testCreateReservationWithMissingFields(): void
    {
        $this->client->request(
            'POST',
            '/api/reservations',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'roomId' => 1,
                'row' => 1
                // Missing seat and customerEmail
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $content);
    }
}
