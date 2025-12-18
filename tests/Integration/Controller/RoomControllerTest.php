<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RoomControllerTest extends WebTestCase
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

    public function testGetRoomsReturnsEmptyArray(): void
    {
        $this->client->request('GET', '/api/rooms');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($content);
    }

    public function testCreateRoomWithValidData(): void
    {
        $this->client->request(
            'POST',
            '/api/rooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Cinema Hall 1',
                'rows' => 5,
                'seatsPerRow' => 10
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertEquals('Cinema Hall 1', $content['name']);
        $this->assertEquals(5, $content['rows']);
        $this->assertEquals(10, $content['seatsPerRow']);
        $this->assertEquals(50, $content['totalSeats']);
    }

    public function testCreateRoomWithMissingFields(): void
    {
        $this->client->request(
            'POST',
            '/api/rooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Cinema Hall 2'
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $content);
    }

    public function testGetRoomsAfterCreation(): void
    {
        // Tworzenie sali
        $this->client->request(
            'POST',
            '/api/rooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test Room',
                'rows' => 3,
                'seatsPerRow' => 4
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Pobieranie wszystkich sal
        $this->client->request('GET', '/api/rooms');

        $this->assertResponseIsSuccessful();
        
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($content);
        $this->assertNotEmpty($content);
        
        $room = $content[0];
        $this->assertArrayHasKey('id', $room);
        $this->assertArrayHasKey('name', $room);
        $this->assertArrayHasKey('rows', $room);
        $this->assertArrayHasKey('seatsPerRow', $room);
        $this->assertArrayHasKey('totalSeats', $room);
        $this->assertArrayHasKey('availableSeats', $room);
        $this->assertArrayHasKey('seats', $room);
        
        // Sprawdzenie tablicy miejsc
        $this->assertIsArray($room['seats']);
        $this->assertCount(12, $room['seats'], 'Expected 12 seats (3 rows * 4 seats), got ' . count($room['seats']));
        
        // Sprawdzenie struktury pierwszego miejsca
        $seat = $room['seats'][0];
        $this->assertArrayHasKey('id', $seat);
        $this->assertArrayHasKey('row', $seat);
        $this->assertArrayHasKey('number', $seat);
        $this->assertArrayHasKey('isReserved', $seat);
        $this->assertFalse($seat['isReserved']);
    }

    public function testUpdateRoom(): void
    {
        // Tworzenie sali
        $this->client->request(
            'POST',
            '/api/rooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Original Name',
                'rows' => 2,
                'seatsPerRow' => 3
            ])
        );

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $roomId = $createResponse['id'];

        // Aktualizacja sali
        $this->client->request(
            'PUT',
            '/api/rooms/' . $roomId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Updated Name',
                'rows' => 4,
                'seatsPerRow' => 5
            ])
        );

        $this->assertResponseIsSuccessful();
        
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Updated Name', $content['name']);
        $this->assertEquals(4, $content['rows']);
        $this->assertEquals(5, $content['seatsPerRow']);
        $this->assertEquals(20, $content['totalSeats']);
    }

    public function testDeleteRoom(): void
    {
        // Tworzenie sali
        $this->client->request(
            'POST',
            '/api/rooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Room to Delete',
                'rows' => 2,
                'seatsPerRow' => 2
            ])
        );

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $roomId = $createResponse['id'];

        // Usuwanie sali
        $this->client->request('DELETE', '/api/rooms/' . $roomId);

        $this->assertResponseIsSuccessful();
        
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $content);
    }

    public function testDeleteNonExistentRoom(): void
    {
        $this->client->request('DELETE', '/api/rooms/99999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $content);
    }
}
