<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\DonneeDuJour;
use App\Form\DonneeDuJourType;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            
            return $this->redirectToRoute('app_logout');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
       
        return $this->render('security/login.html.twig', [
            'last_username'         => $lastUsername, 
            'error'                 => $error,
            ]
        );
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {   
        return $this->render('security/login.html.twig');
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/forgot_passord", name = "forgot_password")
     */
    public function forgotPassword(Request $request, UserPasswordEncoderInterface $passEnc, UserRepository $repoUser, EntityManagerInterface $manager, MailerInterface $mailerInterface)
    {
        if ($request->request->count() > 0) {
            $adresse = $request->request->get('email');
            $user = $repoUser->findOneByEmail($adresse);
           if($user){
                $pass = "password" . rand(0, 100);
                $message = "Votre nouveau mot de passe est:<b>". $pass ."</b>";
                $hash = $passEnc->encodePassword($user, $pass);
                $user->setPassword($hash);
                $manager->flush();
                // Ici nous enverrons l'e-mail
                $email = (new Email())
                ->from('contact@dashboardsounds.com')
                ->to($adresse)
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject("Mis à jour de votre accès au compte dashboardsounds.com")
                //->text('Sending emails is fun again!')
                ->html('<p>' . $message . '</p>');

                $mailerInterface->send($email);
                return $this->render('security/forgot_password.html.twig', [
                    'mp' => "Un message vient d'être envoyer à votre adresse email, <br>Veuiller consulter votre boite de messagerie", 
                ]);
           }
           else{
                return $this->render('security/forgot_password.html.twig', [
                    'mp' => "Cet adresse n'existe pas en tant qu'adresse d'utilisateur",
                ]); 
           }          
        }
        return $this->render('security/forgot_password.html.twig');
    }
    /**
     * @Route("/test_auth_tw", name="test_auth_tw")
     */
    public function test_auth_tw(Request $request, UserRepository $repoUser, UserPasswordEncoderInterface $passwordEncoder)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $mail = $request->get('mail');
            // on cherche le mail dans la base de donnée qui correspond à tw
            $user = $repoUser->findOneByEmail($mail);
            $data = json_encode("non");
            if($user != null){
                $son_hotel = $user->gethotel();
                if ($son_hotel == "tous" || $son_hotel == "Tropical wood") {
                    $data = json_encode("oui");
                }
            }
           
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
           
        }
    }

    /**
     * @Route("/test_auth_s", name="test_auth_s")
     */
    public function test_auth_s(Request $request, UserRepository $repoUser, UserPasswordEncoderInterface $passwordEncoder)
    {
        $response = new Response();
        if ($request->isXmlHttpRequest()) {
            $mail = $request->get('mail');
            // on cherche le mail dans la base de donnée qui correspond à tw
            $user = $repoUser->findOneByEmail($mail);
            $data = json_encode("non");
            if ($user != null) {
                $son_hotel = $user->getHotel();
                $array_hotel = ["Royal Beach", "Calypso", "Baobab Tree", "Vanila Hote", "tous_hotel", "tous"];
                if (in_array($son_hotel, $array_hotel)) {
                    $data = json_encode("oui");
                }
            }

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
    }
        
}
