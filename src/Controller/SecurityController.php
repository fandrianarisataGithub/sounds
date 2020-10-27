<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Client;
use App\Entity\DonneeDuJour;
use App\Form\DonneeDuJourType;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
            
            return $this->redirectToRoute('home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
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
    public function forgotPassword(Request $request, UserPasswordEncoderInterface $passEnc, UserRepository $repoUser, EntityManagerInterface $manager, \Swift_Mailer $mailer)
    {
        if ($request->request->count() > 0) {
            $adresse = $request->request->get('email');
            //dd($adresse);
            $user = $repoUser->findOneByEmail($adresse);
            //dd($user);
            $pass = "password";
            $hash = $passEnc->encodePassword($user, $pass);
            $user->setPassword($hash);
            $manager->flush();
            // Ici nous enverrons l'e-mail
            $message = (new \Swift_Message('Nouveau contact'))
            // On attribue l'expéditeur
            ->setFrom("fandrianarisata2@gmail.com") // le mpandefa

            // On attribue le destinataire
            ->setTo($adresse) // le mpandray

                // On crée le texte avec la vue
                ->setBody(
                    $this->renderView(
                        'emails/mail.html.twig',
                        ['pass' => $pass]
                    ),
                    'text/html'
                );
                //dd($message);
            $mailer->send($message);

            $this->addFlash('message', 'Votre message a été transmis, nous vous répondrons dans les meilleurs délais.'); // Permet un message flash de renvoi
            
        }
        return $this->render('security/forgot_password.html.twig');
    }

   

    
    
        
}
