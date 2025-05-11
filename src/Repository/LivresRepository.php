<?php

namespace App\Repository;

use App\Entity\Livres;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livres>
 */
class LivresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livres::class);
    }
    public function searchByField(string $field, string $term): array
    {
        $qb = $this->createQueryBuilder('l');

        if ($field === 'categorie.libelle') {
            $qb->join('l.categorie', 'c')
                ->andWhere('c.libelle LIKE :term');
        } else {
            // Avoid SQL injection by only allowing certain fields
            if (!in_array($field, ['titre', 'editeur'])) {
                $field = 'titre';
            }
            $qb->andWhere("l.$field LIKE :term");
        }

        $qb->setParameter('term', '%' . $term . '%');
        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Livres[] Returns an array of Livres objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Livres
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
