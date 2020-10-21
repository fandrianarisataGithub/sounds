<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Hotel;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class AppUserAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );
        //dd($credentials);

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException("L'adresse email entré n'existe pas");
        }
        //dd($user);

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }
        // on va initialiser une variable de session 

        //dd($request); il y a l'a propos de user courant
        $email = $request->request->get('email');
        //dd($email);
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        $user_hotel = $user->getHotel();
        $l_hotel = $this->entityManager->getRepository(Hotel::class)->findOneBy(['nom' => $user_hotel]);
        
        if(!$l_hotel){
            
            $hotel_cible = 'tous';

            $session  = $request->getSession();

            // le nom de l'hotel qui va s'ouvrir pour les admin seulement

            $hotel = $session->get('hotel', []);

            // initialisation de la variable session hotel

            $hotel['pseudo_hotel'] = $hotel_cible;
            $hotel['current_page'] = "crj";
            //dd($hotel['pseudo_hotel']);
            // on stock ça dans la variable de session 

            $session->set("hotel", $hotel);
            return new RedirectResponse($this->urlGenerator->generate('hebergement', ['pseudo_hotel' => $hotel['pseudo_hotel']]));
        }
        else{
            //dd($l_hotel);
            $hotel_cible = $l_hotel->getPseudo();
            $session  = $request->getSession();
            // le nom de l'hotel qui va s'ouvrir pour les admin seulement
            $hotel = $session->get('hotel', []);
            // initialisation de la variable session hotel
            $hotel['pseudo_hotel'] = $hotel_cible;
            $hotel['current_page'] = "crj";
            //dd($hotel['pseudo_hotel']);
            // on stock ça dans la variable de session 

            $session->set("hotel", $hotel);

            return new RedirectResponse($this->urlGenerator->generate('hebergement', ['pseudo_hotel' => $hotel['pseudo_hotel']]));
       // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
        }
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
