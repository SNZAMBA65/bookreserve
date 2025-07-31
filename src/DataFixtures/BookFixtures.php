<?php
// src/DataFixtures/BookFixtures.php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $books = [
            ['title' => 'Le Petit Prince', 'isbn' => '978-2070612758'],
            ['title' => '1984', 'isbn' => '978-0451524935'],
            ['title' => 'Harry Potter à l\'école des sorciers', 'isbn' => '978-2070584624'],
            ['title' => 'L\'Étranger', 'isbn' => '978-2070360024'],
            ['title' => 'Pride and Prejudice', 'isbn' => '978-0141439518'],
        ];

        foreach ($books as $bookData) {
            $book = new Book();
            $book->setTitle($bookData['title']);
            $book->setIsbn($bookData['isbn']);
            $book->setAvailable(true);
            
            $manager->persist($book);
        }

        $manager->flush();
    }
}