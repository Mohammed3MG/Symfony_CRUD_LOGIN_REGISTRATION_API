<?php

namespace App\Repository;

use App\Entity\UserSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSession::class);
    }

    // Custom method to find valid (non-expired) session by token
    public function findValidSession(string $token): ?UserSession
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.token = :token')
            ->andWhere('s.expiresAt IS NULL OR s.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
