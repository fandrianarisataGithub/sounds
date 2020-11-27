<?php

namespace App\Repository;

use App\Entity\DataTropicalWood;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method DataTropicalWood|null find($id, $lockMode = null, $lockVersion = null)
 * @method DataTropicalWood|null findOneBy(array $criteria, array $orderBy = null)
 * @method DataTropicalWood[]    findAll()
 * @method DataTropicalWood[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DataTropicalWoodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataTropicalWood::class);
    }
     /**
     * @return DataTropicalWood[] Returns an array of DataTropicalWood objects
     */
    public function searchEntrepriseContact(string $value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.entreprise LIKE :val OR d.contact LIKE :val')
            ->setParameter('val', '%' . $value . '%')
            ->getQuery()
            ->getResult();

    }

    /**
     * @return DataTropicalWood[] Returns an array of DataTropicalWood objects
     */
    public function searchTypeTransaction(string $value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.type_transaction LIKE :val')
            ->setParameter('val', '%' . $value . '%')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return DataTropicalWood[] Returns an array of DataTropicalWood objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DataTropicalWood
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
