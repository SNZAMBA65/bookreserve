<?php


namespace App\Controller;

use App\Entity\Book;
use App\Entity\Reservation;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/books', name: 'books_', format: 'json')]
class BookController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(BookRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $books = $repository->findAvailableBooks();
        
        $data = $serializer->serialize($books, 'json', [
            'attributes' => ['id', 'title', 'isbn', 'available', 'createdAt']
        ]);

        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}/reserve', name: 'reserve', methods: ['POST'])]
    public function reserve(
        Book $book,
        EntityManagerInterface $em,
        Request $request,
        ValidatorInterface $validator
    ): JsonResponse {
        // Vérifier si le livre est disponible
        if (!$book->isAvailable()) {
            return $this->json([
                'error' => 'Ce livre est déjà réservé'
            ], 400);
        }

        // Récupérer l'email depuis la requête
        $data = json_decode($request->getContent(), true);
        $userEmail = $data['userEmail'] ?? null;

        if (!$userEmail) {
            return $this->json([
                'error' => 'L\'email utilisateur est requis'
            ], 400);
        }

        // Valider l'email
        if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'error' => 'Format d\'email invalide'
            ], 400);
        }

        // Créer la réservation
        $reservation = new Reservation();
        $reservation->setBook($book);
        $reservation->setUserEmail($userEmail);

        // Marquer le livre comme non disponible
        $book->setAvailable(false);

        $em->persist($reservation);
        $em->flush();

        return $this->json([
            'message' => 'Livre réservé avec succès',
            'reservation' => [
                'id' => $reservation->getId(),
                'book_title' => $book->getTitle(),
                'user_email' => $reservation->getUserEmail(),
                'reserved_at' => $reservation->getReservedAt()->format('Y-m-d H:i:s'),
                'expires_at' => $reservation->getExpiresAt()->format('Y-m-d H:i:s')
            ]
        ], 201);
    }
}