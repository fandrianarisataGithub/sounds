<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/lister_user", name="setting")
     */
    public function index()
    {
        return $this->render('admin/setting.html.twig');
    }

    /**
     * @Route("/admin/insert_user_by_login", name="insert.by.login")
     */
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passEnc)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
       if ($form->isSubmitted() && $form->isValid()) {
            //dd($form->getData());
            $user = $form->getData();
            $user->setHotel("tous");
            $user->setRoles(["ROLE_ADMIN"]);
            $hash = $passEnc->encodePassword($user, $form->get('password')->getData());
            $user->setPassword($hash);
             $entityManager->persist($user);
            $entityManager->flush();
            //dd($user);
        }
        return $this->render('admin/registerByLogin.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("admin/register_user", name = "admin.register")
     */
    public function adminRegister(Request $request, EntityManagerInterface $manager, UserRepository $repo, UserPasswordEncoderInterface $passEnc)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            // receuil des data

            $role = $request->get('role');
            
            $hotel = $request->get('hotel');
            $nom = $request->get('nom');
            $prenom = $request->get('prenom') ? $request->get('prenom'):"";
            $email = $request->get('email');
            $password = $request->get('password');
            $username = $request->get('username');
            $erreur = 0;
            $user = new User();
            //$data = "role=".$role."/hotel=".$hotel."/nom=".$nom."/email=".$email."/prenom=".$prenom."/password=".$password;
            //return new JsonResponse(array("data" => json_encode($data)));

            if(!empty($email) && !empty($password) && !empty($nom)) {
                    $user = new User();
                    $tab = $repo->findBy(array("email"=>$email));
                    $taille = count($tab);

                    if($taille == 1){
                        // erreur email déjà utilisé

                        $response->headers->set('Content-Type', 'application/json');
                        $response->setContent("2");
                    }
                    // si ce user n'est pas là
                    else if($taille == 0){
                        $user->setEmail($email);
                        // hasher le password 
                        $hash = $passEnc->encodePassword($user, $password);
                        $user->setPassword($hash);
                        $user->setNom($nom);
                        $user->setPrenom($prenom);
                        //par défaut son username est la première partie de son nom 
                        $user->setUsername($username);
                        // le role 
                        
                        if($role == "administrateur"){
                            $user->setRoles(array('ROLE_ADMIN'));
                        }
                        else if($role == "editeur") {
                            $user->setRoles(array('ROLE_USER'));
                        }

                        $user->setHotel($hotel);
                        // on persist 
                        $manager->persist($user);
                        // on flush 
                        $manager->flush();

                        // on récupère tous les users
                        $tab_user = $this->getDoctrine()
                        ->getRepository(User::class)
                        ->findAll();
                        // on stock ces data dans u tableau 
                        $tab = array();
                        //var d incrementation pour lister les data
                        $i = 0;
                        foreach ($tab_user as $t) {
                            $tab[$i]['nom'] = $t->getNom();
                            $tab[$i]['prenom'] = $t->getPrenom();
                            $tab[$i]['hotel'] = $t->getHotel();
                            $tab[$i]['id'] = $t->getId();
                            $i++;
                        }

                        $data = json_encode($tab); // formater le résultat de la requête en json

                        $response->headers->set('Content-Type', 'application/json');
                        $response->setContent($data);
                        /*$data = json_encode($tab);
                        $erreur = $data;*/

                    }
                
                   
            }
            else {
                // erreur email et pass vide ou le nom

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent("1");
            }

            return $response;

        }
        
        return $this->render('security/setting.html.twig');
    }


    /**
     * @Route("/security/delete_user/{id}", name = "delete.user")
     */
    public function delete_user($id, Request $request, EntityManagerInterface $manager, UserRepository $repoUser)
    {
        $response = new Response();
        $user =  new User();
        if ($request->isXmlHttpRequest()) {
            $action = $request->get('action');
            if ($action == "delete") {
                $user = $repoUser->find($id);
                $manager->remove($user);
                $manager->flush();

                // liste des user 
                // on récupère tous les users
                $tab_user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->findAll();
                // on stock ces data dans u tableau 
                $tab = array();

                //var d incrementation pour lister les data
                $i = 0;
                foreach ($tab_user as $t) {
                    $tab[$i]['nom'] = $t->getNom();
                    $tab[$i]['prenom'] = $t->getPrenom();
                    $tab[$i]['hotel'] = $t->getHotel();
                    $tab[$i]['id'] = $t->getId();
                    $i++;
                }
                $data = json_encode($tab); // formater le résultat de la requête en json

                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
                return $response;
            }
        }
    }


   
    
}
