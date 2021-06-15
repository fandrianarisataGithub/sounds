<?php

namespace App\Repository;

use App\Entity\Hotel;
use App\Entity\Client;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }
    public function findAll()
    {
        return $this->findBy(array(), array('createdAt' => 'DESC'));
    }
    //

    public function findClientBetweenTwoDates($hotel, $date1, $date2)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.hotel = :hotel')
            ->andWhere('c.dateArrivee >= :date1 AND c.dateDepart <= :date2')
            ->setParameter('hotel' , $hotel)
            ->setParameter('date1', $date1)
            ->setParameter('date2', $date2)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findTotalByTelephone($id)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT COUNT(DISTINCT telephone,  
            nom, prenom) AS total_client
            FROM client
            WHERE fidelisation_id = :id AND email IS NULL
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    public function findTotalByEmail($id)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT COUNT(DISTINCT email,  
            nom, prenom) AS total_client
            FROM client
            WHERE fidelisation_id = :id
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    public function findTotalCaFid($id)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT SUM(client.prix_total) AS total_ca
            WHERE client.fidelisation_id = :id
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    public function findDatasForHome()
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql_clients_distinct = '
            SELECT DISTINCT email, telephone, client.nom as client_nom, client.prenom, fidelisation.nom as fid_nom, fidelisation.id as fid_id, fidelisation.icone_carte, fidelisation.icone_client, fidelisation.style_etiquette FROM `client`
            INNER JOIN `fidelisation` 
            ON client.fidelisation_id = fidelisation.id
        ';
        
        $stmt_clients_distinct = $conn->prepare($sql_clients_distinct);
        $stmt_clients_distinct->execute();

        // returns an array of arrays (i.e. a raw data set)
        $clients_fid_distinct = $stmt_clients_distinct->fetchAll();

        $sql_chiffre = '
            SELECT SUM(client.duree_sejour) AS total_nuitee, SUM(client.prix_total) AS rev_global  FROM `client`
            WHERE client.fidelisation_id IS NOT NULL
        ';
        $stmt_chiffre = $conn->prepare($sql_chiffre);
        $stmt_chiffre->execute();

        $chiffres = $stmt_chiffre->fetch();

        // data for graph

        $sql_graph = '
            SELECT client.provenance, COUNT(client.provenance) AS effectif FROM `client`
            INNER JOIN `fidelisation` 
            ON client.fidelisation_id = fidelisation.id
            WHERE client.provenance IS NOT NULL
            GROUP BY client.provenance
        ';

        $stmt_graph = $conn->prepare($sql_graph);
        $stmt_graph->execute();

        $data_graph = $stmt_graph->fetchAll();

        // statistique group by fide nom

        $sql_group_by_fid = '
            SELECT fidelisation.nom AS nom_fid, COUNT(DISTINCT client.email, client.telephone) AS effectif FROM client
            INNER JOIN fidelisation
            ON fidelisation.id = client.fidelisation_id
            GROUP BY (fidelisation.nom)
        ';

        $stmt_fid_group_by = $conn->prepare($sql_group_by_fid);
        $stmt_fid_group_by->execute();

        $data_fid_group_by = $stmt_fid_group_by->fetchAll();

        $datas = [
            "clients"   => $clients_fid_distinct,
            "chiffres"  => $chiffres,
            "graph"     => $data_graph,
            "stat"      => [
                0 => isset($data_fid_group_by[0]) ? $data_fid_group_by[0]['effectif'] : "0",
                1 => isset($data_fid_group_by[1]) ? $data_fid_group_by[1]['effectif'] : "0",
                2 => isset($data_fid_group_by[2]) ? $data_fid_group_by[2]['effectif'] : "0",
                3 => isset($data_fid_group_by[3]) ? $data_fid_group_by[3]['effectif'] : "0",
            ]
        ];

        //dd($datas);

        return $datas;
        
        
    }

    public function findAllClientsFidById($id_fid)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql_clients = '
        SELECT DISTINCT email, telephone, client.nom as client_nom, client.prenom, fidelisation.nom as fid_nom, fidelisation.id as fid_id, fidelisation.icone_carte, fidelisation.icone_client, fidelisation.style_etiquette FROM `client`
        INNER JOIN `fidelisation` 
        ON client.fidelisation_id = fidelisation.id
        WHERE fidelisation_id =:id_fid
            ';
        $stmt_clients = $conn->prepare($sql_clients);
        $stmt_clients->execute(['id_fid' => $id_fid]);

        // returns an array of arrays (i.e. a raw data set)
        $clients =  $stmt_clients->fetchAll();
        $sql_total_prix = '
            SELECT SUM(prix_total) AS ca FROM `client`
            INNER JOIN `fidelisation`
            ON client.fidelisation_id = fidelisation.id
            WHERE fidelisation_id = :id_fid
        ';
        $stmt_total_prix = $conn->prepare($sql_total_prix);
        $stmt_total_prix->execute(['id_fid' => $id_fid]);
       //$stmt_total_prix->execute();
        $prix_total = $stmt_total_prix->fetch();

        $data_fid = [
            "nbr_clients" => count($clients),
            "rev_global"  => $prix_total['ca'],
            "clients"     => $clients
        ];
        return $data_fid;
    }

    public function selectDistinc($nom)
    {
        return $this->createQueryBuilder('c')
            ->select('c.nom, c.prenom, c.email, c.telephone')
            ->where('c.nom LIKE :word')
            ->setParameter('word', $nom.'%')
            ->distinct()
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findAllForVue()
    {
        return $this->createQueryBuilder('c')
            ->select('c.nom, c.prenom, c.email, c.telephone, c.id')
            ->distinct()
            ->getQuery()
            ->getResult()
        ; 
    }

    public function findClientByContact($contact)
    {
        // si le contact est un valide email
        if(filter_var($contact, FILTER_VALIDATE_EMAIL)){
            return $this->createQueryBuilder('c')
                ->andWhere('c.email = :email')
                ->setParameter('email', $contact)
                ->leftJoin('App\Entity\Hotel', 'h', 'WITH', 'h.id = c.hotel')
                ->addSelect('h.nom AS nom_hotel')
                ->getQuery()
                ->getResult()
            ; 
        }else{
            return $this->createQueryBuilder('c')
                    ->andWhere('c.telephone = :phone')
                    ->setParameter('phone', $contact)
                    ->leftJoin('App\Entity\Hotel', 'h', 'WITH', 'h.id = c.hotel')
                    ->addSelect('h.nom AS nom_hotel')
                    ->getQuery()
                    ->getResult()
                ; 
        }
       
    }

    public function searchClientsByTabIdentifiant($tab, \DateTime $start)
    {

        if($tab["mail"]){
            return $this->createQueryBuilder('c')
                ->andWhere('c.email = :val')
                ->andWhere('c.createdAt >= :start')
                ->setParameter('start', $start)
                ->setParameter('val', $tab["mail"])
                ->orderBy('c.createdAt', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }
        
        if($tab["telephone"]){
            return $this->createQueryBuilder('c')
                ->andWhere('c.telephone = :val')
                ->andWhere('c.createdAt >= :start')
                ->setParameter('start', $start)
                ->setParameter('val', $tab["telephone"])
                ->orderBy('c.createdAt', 'ASC')
                ->getQuery()
                ->getResult()
            ;
        }
        
    }
    
}
