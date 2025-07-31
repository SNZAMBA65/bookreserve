<?php


namespace App\Tests\Controller;

use App\Entity\Book;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        
        // Nettoyer la base de données
        $this->entityManager->createQuery('DELETE FROM App\Entity\Reservation')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Book')->execute();
    }

    public function testListBooks(): void
    {
        // Créer des livres de test
        $book1 = new Book();
        $book1->setTitle('Test Book 1');
        $book1->setIsbn('978-1234567890');
        $book1->setAvailable(true);

        $book2 = new Book();
        $book2->setTitle('Test Book 2');
        $book2->setIsbn('978-0987654321');
        $book2->setAvailable(false);

        $this->entityManager->persist($book1);
        $this->entityManager->persist($book2);
        $this->entityManager->flush();

        // Tester la route
        $this->client->request('GET', '/api/books');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        // Vérifier qu'on récupère seulement les livres disponibles
        $this->assertCount(1, $responseData);
        $this->assertEquals('Test Book 1', $responseData[0]['title']);
    }

    public function testReserveBook(): void
    {
        // Créer un livre de test
        $book = new Book();
        $book->setTitle('Test Book');
        $book->setIsbn('978-1234567890');
        $book->setAvailable(true);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        // Tester la réservation réussie
        $this->client->request('POST', '/api/books/' . $book->getId() . '/reserve', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'userEmail' => 'test@example.com'
        ]));

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Livre réservé avec succès', $responseData['message']);
        $this->assertEquals('test@example.com', $responseData['reservation']['user_email']);

        // Vérifier que le livre n'est plus disponible
        $this->entityManager->refresh($book);
        $this->assertFalse($book->isAvailable());
    }

    public function testReserveAlreadyReservedBook(): void
    {
        // Créer un livre déjà réservé
        $book = new Book();
        $book->setTitle('Test Book');
        $book->setIsbn('978-1234567890');
        $book->setAvailable(false);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        // Tenter de réserver le livre
        $this->client->request('POST', '/api/books/' . $book->getId() . '/reserve', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'userEmail' => 'test@example.com'
        ]));

        $this->assertResponseStatusCodeSame(400);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Ce livre est déjà réservé', $responseData['error']);
    }

    public function testReserveBookWithoutEmail(): void
    {
        // Créer un livre de test
        $book = new Book();
        $book->setTitle('Test Book');
        $book->setIsbn('978-1234567890');
        $book->setAvailable(true);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        // Tenter de réserver sans email
        $this->client->request('POST', '/api/books/' . $book->getId() . '/reserve', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        $this->assertResponseStatusCodeSame(400);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('L\'email utilisateur est requis', $responseData['error']);
    }

    public function testReserveBookWithInvalidEmail(): void
    {
        // Créer un livre de test
        $book = new Book();
        $book->setTitle('Test Book');
        $book->setIsbn('978-1234567890');
        $book->setAvailable(true);

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        // Tenter de réserver avec un email invalide
        $this->client->request('POST', '/api/books/' . $book->getId() . '/reserve', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'userEmail' => 'invalid-email'
        ]));

        $this->assertResponseStatusCodeSame(400);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Format d\'email invalide', $responseData['error']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}