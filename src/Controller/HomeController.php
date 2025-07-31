<?php
// src/Controller/HomeController.php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(BookRepository $bookRepository): Response
    {
        $allBooks = $bookRepository->findAll();
        $availableBooks = $bookRepository->findAvailableBooks();
        
        return $this->render('home/index.html.twig', [
            'books' => $allBooks,
            'available_books' => $availableBooks,
            'total_books' => count($allBooks),
            'available_count' => count($availableBooks),
            'reserved_count' => count($allBooks) - count($availableBooks)
        ]);
    }
}