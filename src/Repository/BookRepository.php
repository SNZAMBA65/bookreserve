<?php
// src/Repository/BookRepository.php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @return Book[] Returns an array of available books
     */
    public function findAvailableBooks(): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.available = :available')
            ->setParameter('available', true)
            ->orderBy('b.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Book[] Returns an array of all books
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}