<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\HotelRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
     * @Route("/profile/insert_user_by_login", name="insert.by.login")
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
     * @Route("/profile/register_user", name = "admin.register")
     */
    public function adminRegister(Request $request, EntityManagerInterface $manager, UserRepository $repo, UserPasswordEncoderInterface $passEnc, HotelRepository $reposHotel)
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
                        $data = json_encode("L'adresse mail est déjà utilisé");
                        $response->headers->set('Content-Type', 'application/json');
                        $response->setContent($data);
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
                        if ($role == "super_admin") {
                            $user->setRoles(array('ROLE_ADMIN'));
                            $user->setProfile("super_admin"); // acces à tous
                            // on select tous les hotels
                            $hotels = $reposHotel->findAll();
                            foreach ($hotels as $h) {
                                $user->addHotel($h);
                            }
                        }
                       else if($role == "admin_all_hotels"){
                            $user->setRoles(array('ROLE_ADMIN'));
                            $user->setProfile("admin_all_hotels"); // acces unique pour tous les hotel
                            // on select tous les hotels
                            $hotels = $reposHotel->findAll();
                            foreach($hotels as $h){
                                $son_nom = $h->getNom();
                                // il ne faut pas que les noms d'hôtel autre que les hôtels entre dans cette liste
                                if($son_nom != "Tropical wood"){
                                    $user->addHotel($h);
                                }
                            }
                        }
                        else if($role == "editeur") {
                            $user->setRoles(array('ROLE_USER'));
                            $user->setProfile("admin_hotel"); // hotel unique
                            $hotels = $reposHotel->findOneByNom($hotel);
                            $user->addHotel($hotels);
                        } 
                        else if ($role == "tropical_wood") {
                            $user->setProfile("admin_tropical_wood");
                            $user->setRoles(array('ROLE_TROPICAL_WOOD'));
                            $hotels = $reposHotel->findOneByNom($hotel);
                            $user->addHotel($hotels);
                        }

                        else if ($role == "receptionniste") {
                            $user->setRoles(array('ROLE_USER'));
                            $user->setProfile("admin_hotel"); // hotel unique
                            $user->setReceptionniste('oui'); // stria tsy tadidiko tsoony ze moimba anle profile tany
                            $hotels = $reposHotel->findOneByNom($hotel);
                            $user->addHotel($hotels);
                        }

                        else if ($role == "comptable") {
                            $user->setRoles(array('ROLE_USER'));
                            $user->setProfile("admin_hotel"); // hotel unique
                            $user->setComptable('oui'); // stria tsy tadidiko tsoony ze moimba anle profile tany
                            $hotels = $reposHotel->findOneByNom($hotel);
                            $user->addHotel($hotels);
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
                        $data = json_encode("ok"); // formater le résultat de la requête en json
                        $response->headers->set('Content-Type', 'application/json');
                        $response->setContent($data);
                    }                   
            }
            else {
                // erreur email et pass vide ou le nom
                $data = json_encode("Veuiller remplir tous les champs");
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
            }

            return $response;

        }
        
    }

    /**
     * @Route("/profile/print_all_users", name="print_all_users")
     */
    public function print_all_users(Request $request, UserRepository $repoUser, HotelRepository $repoHotel)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){

            // users
            $users = $repoUser->findAll();
            $html = "";
            foreach($users as $elm){
               
                $son_role = "";
                $son_profile = $elm->getProfile();
                $son_recep = $elm->getReceptionniste();
                $son_comptable = $elm->getComptable();
                $son_nom_hotel = "";
                // les admin : super admin et admin all hotels
               
                if ($son_profile == "super_admin") {
                    $son_role = "Super Admin";
                    $son_nom_hotel = "Accès à tout";
                }
                else if($son_profile == "admin_all_hotels"){
                    $son_role = 'Admin hôtels';
                    $son_nom_hotel = "Accès à tous les hôtels";
                }
                else if($son_profile == "admin_hotel" && $son_recep != "oui"  && $son_comptable != "oui"){
                    $son_role = 'Admin '. $elm->getHotel();
                    $son_nom_hotel = "Accès simple";
                } 
                else if($son_profile == "admin_hotel" && $son_recep == "oui"){
                    $son_role = 'Receptionniste '. $elm->getHotel();
                    $son_nom_hotel = "Accès simple";
                }
                else if($son_profile == "admin_hotel" && $son_comptable == "oui"){
                    $son_role = 'Comptable '. $elm->getHotel();
                    $son_nom_hotel = "Accès simple";
                } 
                else if ($son_profile == "admin_tropical_wood") {
                    $son_role = 'Admin Tropical wood';
                    $son_nom_hotel = "Accès simple";
                }

                $html .= '
                    <li>
                        <span class="nom_pers">
                            ' . $elm->getNom() . '
                            <br>
                            ' . $elm->getPrenom() . '
                        </span>
                        <span class="role_pers">
                            ' . $son_role . '</br>' . $son_nom_hotel . '
                        </span>
                        <div>
                            <a href="#" data-id = "' . $elm->getId() . '"  data-target="#modal_form_modif_admin" class = "edit_user">
                                <span class="fa fa-edit"></span>
                            </a>
                            <a href="#" data-id = "' . $elm->getId() . '" data-toggle="modal" data-target="#modal_form_confirme_pers" class="delete_user">
                                <span class="fa fa-trash-o"></span>
                            </a>
                        </div>
                    </li>
                ';
            }
            $data = json_encode($html); // formater le résultat de la requête en json

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
        }
        else{
            // users
            $users = $repoUser->findAll();
            $html = "";
            foreach ($users as $elm) {

                $son_role = "";
                $son_profile = $elm->getProfile();
                $son_nom_hotel = "";
                
                // les admin : super admin et admin all hotels

                if ($son_profile == "super_admin") {
                    $son_role = "Super Admin";
                    $son_nom_hotel = "Accès à tout";
                } else if ($son_profile == "admin_all_hotels") {
                    $son_role = 'Admin hôtels';
                    $son_nom_hotel = "Accès à tous les hôtels";
                } else if ($son_profile == "editeur") {
                    $son_role = 'Admin ' . $elm->getHotel();
                    $son_nom_hotel = "Accès simple";
                } else if ($son_profile == "admin_tropical_wood") {
                    $son_role = 'Admin Tropical wood';
                    $son_nom_hotel = "Accès simple";
                }

                $html .= '
                    <li>
                        <span class="nom_pers">
                            ' . $elm->getNom() . '
                            <br>
                            ' . $elm->getPrenom() . '
                        </span>
                        <span class="role_pers">
                            ' . $son_role . '</br>' . $son_nom_hotel . '
                        </span>
                        <div>
                            <a href="#" data-id = "' . $elm->getId() . '"  data-target="#modal_form_modif_admin" class = "edit_user">
                                <span class="fa fa-edit"></span>
                            </a>
                            <a href="#" data-id = "' . $elm->getId() . '" data-toggle="modal" data-target="#modal_form_confirme_pers" class="delete_user">
                                <span class="fa fa-trash-o"></span>
                            </a>
                        </div>
                    </li>
                ';
            }
                dd($html);
        }
            
                
    }

    /**
     * @Route("/profile/pick_up/{id}", name = "pick_up_user")
     */
    public function pick_up_user($id, UserRepository $repoUser, Request $request)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $user = $repoUser->find($id);
            
            $html = '';

            $son_role = $user->getRoles();
            $t_role = count($son_role);
            $select_role = '';
            $select_hotel = '';
            $son_hotel = $user->getHotel();
            $select_role = '
                <select name="" id="modal_choix_role" class="form-control">';
            $son_profile = $user->getProfile();
            if($son_profile == "super_admin"){
                $select_role .= '
                    <option selected="selected" value="super_admin">Super admin</option>
                    <option value="admin_all_hotels">Admin des hôtels</option>
                    <option value="editeur">Editeur pour un hôtel</option>
                    <option value="tropical_wood">Admin Tropical Wood</option>
                    <option value="receptionniste">Receptionniste</option>
                    <option value="comptable">Comptable</option>
                </select>
                ';
            }
            else if ($son_profile == "admin_all_hotels") {
                $select_role .= '
                    <option  value="super_admin">Super admin</option>
                    <option selected="selected" value="admin_all_hotels">Admin des hôtels</option>
                    <option value="editeur">Editeur pour un hôtel</option>
                    <option value="tropical_wood">Admin Tropical Wood</option>
                    <option value="receptionniste">Receptionniste</option>
                    <option value="comptable">Comptable</option>
                </select>
                ';
            }
            else if ($son_profile == "admin_hotel") {
                if($user->getReceptionniste() == "oui"){
                    $select_role .= '
                            <option  value="super_admin">Super admin</option>
                            <option  value="admin_all_hotels">Admin des hôtels</option>
                            <option value="editeur">Editeur pour un hôtel</option>
                            <option value="tropical_wood">Admin Tropical Wood</option>
                            <option selected="selected" value="receptionniste">Receptionniste</option>
                            <option value="comptable">Comptable</option>
                        </select>
                    ';
                }
                else if($user->getComptable() == "oui"){
                    $select_role .= '
                            <option value="super_admin">Super admin</option>
                            <option value="admin_all_hotels">Admin des hôtels</option>
                            <option value="editeur">Editeur pour un hôtel</option>
                            <option value="tropical_wood">Admin Tropical Wood</option>
                            <option value="receptionniste">Receptionniste</option>
                            <option selected="selected" value="comptable">Comptable</option>
                        </select>
                    ';
                }
                else{
                    $select_role .= '
                            <option value="super_admin">Super admin</option>
                            <option value="admin_all_hotels">Admin des hôtels</option>
                            <option selected="selected" value="editeur">Editeur pour un hôtel</option>
                            <option value="tropical_wood">Admin Tropical Wood</option>
                            <option value="receptionniste">Receptionniste</option>
                            <option value="comptable">Comptable</option>
                        </select>
                    ';
                }
            }
            else if ($son_profile == "admin_tropical_wood") {
                $select_role .= '
                    <option  value="super_admin">Super admin</option>
                    <option  value="admin_all_hotels">Admin des hôtels</option>
                    <option  value="editeur">Editeur pour un hôtel</option>
                    <option selected="selected" value="tropical_wood">Admin Tropical Wood</option>
                    <option value="receptionniste">Receptionniste</option>
                    <option value="comptable">Comptable</option>
                </select>
                ';
            }
                   
            $select_hotel = '
                <select name="" id="modal_choix_hotel" class="form-control">';
                $son_type_hotel = $user->getHotel();
            if($son_type_hotel == "tous"){
                $select_hotel .= '
                    <option selected="selected" value="tous">Tous</option>
                    <option value="tous_hotel">Tous/Hotels</option>
                    <option value="Royal Beach">Royal Beach</option>
                    <option value="Calypso">Calypso</option>
                    <option value="Baobab Tree">Baobab Tree</option>
                    <option value="Vanila Hotel">Vanila Hotel</option>
                    <option value="Tropical wood">Tropical wood</option>
                </select>
                ';
            }
            else if($son_type_hotel == "tous_hotel") {
                $select_hotel .= '
                    <option value="tous">Tous</option>
                    <option selected="selected" value="tous_hotel">Tous/Hotels</option>
                    <option value="Royal Beach">Royal Beach</option>
                    <option value="Calypso">Calypso</option>
                    <option value="Baobab Tree">Baobab Tree</option>
                    <option value="Vanila Hotel">Vanila Hotel</option>
                    <option value="Tropical wood">Tropical wood</option>
                </select>
                ';
            } 
            else if ($son_type_hotel == "Royal Beach") {
                $select_hotel .= '
                    <option value="tous">Tous</option>
                    <option value="tous_hotel">Tous/Hotels</option>
                    <option selected="selected" value="Royal Beach">Royal Beach</option>
                    <option value="Calypso">Calypso</option>
                    <option value="Baobab Tree">Baobab Tree</option>
                    <option value="Vanila Hotel">Vanila Hotel</option>
                    <option value="Tropical wood">Tropical wood</option>
                </select>
                ';
            } 
            else if ($son_type_hotel == "Calypso") {
                $select_hotel .= '
                    <option value="tous">Tous</option>
                    <option value="tous_hotel">Tous/Hotels</option>
                    <option value="Royal Beach">Royal Beach</option>
                    <option selected="selected" value="Calypso">Calypso</option>
                    <option value="Baobab Tree">Baobab Tree</option>
                    <option value="Vanila Hotel">Vanila Hotel</option>
                    <option value="Tropical wood">Tropical wood</option>
                </select>
                ';
            } else if ($son_type_hotel == "Baobab Tree") {
                $select_hotel .= '
                    <option value="tous">Tous</option>
                    <option value="tous_hotel">Tous/Hotels</option>
                    <option value="Royal Beach">Royal Beach</option>
                    <option value="Calypso">Calypso</option>
                    <option selected="selected" value="Baobab Tree">Baobab Tree</option>
                    <option value="Vanila Hotel">Vanila Hotel</option>
                    <option value="Tropical wood">Tropical wood</option>
                </select>
                ';
            } else if ($son_type_hotel == "Vanila Hotel") {
                $select_hotel .= '
                    <option value="tous">Tous</option>
                    <option value="tous_hotel">Tous/Hotels</option>
                    <option value="Royal Beach">Royal Beach</option>
                    <option value="Calypso">Calypso</option>
                    <option value="Baobab Tree">Baobab Tree</option>
                    <option selected="selected" value="Vanila Hotel">Vanila Hotel</option>
                    <option value="Tropical wood">Tropical wood</option>
                </select>
                ';
            } else if ($son_type_hotel == "Tropical wood") {
                $select_hotel .= '
                    <option value="tous">Tous</option>
                    <option value="tous_hotel">Tous/Hotels</option>
                    <option value="Royal Beach">Royal Beach</option>
                    <option value="Calypso">Calypso</option>
                    <option value="Baobab Tree">Baobab Tree</option>
                    <option value="Vanila Hotel">Vanila Hotel</option>
                    <option selected="selected" value="Tropical wood">Tropical wood</option>
                </select>
                ';
            }
                    
            $html.= '
                <form action="">
						<div class="form-group">
							<label for="choix_role">Type de l\'administrateur :</label>
							'. $select_role . '
						</div>
						<div class="form-group nom_hotel">
							<label for="choix_hotel">Nom de l\'hotel</label>
							' . $select_hotel . '
						</div>

						<div class="form-group">
							<span class="span__label"></span>
							<input type="text" name="" data-placeholder="Nom" value = "'. $user->getNom() . '" class="form-control" id="modal_nom_pers" placeholder="Nom">
						</div>
						<div class="form-group">
							<span class="span__label"></span>
							<input type="text" name="" data-placeholder="Prénom" value = "' . $user->getPrenom() . '" class="form-control" id="modal_prenom_pers" placeholder="Prénom">
						</div>
						<div class="form-group">
							<span class="span__label"></span>
							<input type="text" name="" data-placeholder="Adresse mail" value = "' . $user->getEmail() . '" class="form-control" id="modal_username_pers" placeholder="Adresse mail">
						</div>
						<div class="form-group">
							<button type="submit" class="form-control btn btn-warning" data-id = "' . $user->getId() . '" id="btn_modif_admin"><span>Enregistrer</span></button>
						</div>
					</form>
            ';

            $data = json_encode($html);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response; 
        }
    }

    /**
     * @Route("/admin/edit_user", name = "edit_user")
     */
    public function edit_user(Request $request, UserRepository $repoUser, EntityManagerInterface $manager, HotelRepository $reposHotel)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            
            $id = $request->get('user_id');
            $nom = $request->get('nom_pers');
            $prenom_user = $request->get('prenom_pers');
            $email = $request->get('username_pers');
            $role = $request->get('choix_role');
            $hotel = $request->get('choix_hotel');
            // test validité
            $retour = "";
            $user = $repoUser->find($id);
            if($nom != "" && $email != ""){
                // si l'email est unique
                $user_by_mail = $repoUser->findBy(['email'=>$email]);
                if(count($user_by_mail) == 0){
                    if ($role == "super_admin") {
                        $user->setRoles(array('ROLE_ADMIN'));
                        $user->setProfile("super_admin"); 
                        // acces à tous
                        $user->setHotel("tous");
                        // on select tous les hotels
                        $hotels = $reposHotel->findAll();
                        foreach ($hotels as $h) {
                            $user->addHotel($h);
                        }
                    } else if ($role == "admin_all_hotels") {
                        $user->setRoles(array('ROLE_ADMIN'));
                        $user->setHotel("tous_hotel");
                        $user->setProfile("admin_all_hotels"); 
                        // acces unique pour tous les hotel
                        // on select tous les hotels
                        $hotels = $reposHotel->findAll();
                        foreach ($hotels as $h) {
                            $son_nom = $h->getNom();
                            // il ne faut pas que les noms d'hôtel autre que les hôtels entre dans cette liste
                            if ($son_nom != "Tropical wood") {
                                $user->addHotel($h);
                            }
                        }
                    } else if ($role == "editeur") {
                        $user->setRoles(array('ROLE_USER'));
                        $user->setHotel($hotel);
                        $user->setProfile("admin_hotel"); // hotel unique
                        $hotels = $reposHotel->findOneByNom($hotel);
                        $user->addHotel($hotels);
                    } else if ($role == "tropical_wood") {
                        $user->setProfile("admin_tropical_wood");
                        $user->setHotel($hotel);
                        $user->setRoles(array('ROLE_TROPICAL_WOOD'));
                        $hotels = $reposHotel->findOneByNom($hotel);
                        $user->addHotel($hotels);
                    }else if ($role == "receptionniste") {
                        $user->setProfile("admin_hotel");
                        $user->setReceptionniste("oui");
                        $user->setHotel($hotel);
                        $user->setRoles(array('ROLE_USER'));
                        $hotels = $reposHotel->findOneByNom($hotel);
                        $user->addHotel($hotels);
                    }else if ($role == "comptable") {
                        $user->setProfile("admin_hotel");
                        $user->setComptable("oui");
                        $user->setHotel($hotel);
                        $user->setRoles(array('ROLE_USER'));
                        $hotels = $reposHotel->findOneByNom($hotel);
                        $user->addHotel($hotels);
                    }
                    $user->setNom($nom);
                    $user->setPrenom($prenom_user);
                    $manager->flush();
                    $retour = "ok";
                }
                else{
                    // si il a changé son adresse
                    $son_email = $user->getEmail();
                    if($email != $son_email){
                        $retour = "Cet adresse mail est déjà utilisée ...";
                    }
                    else{
                        if ($role == "super_admin") {
                            $user->setRoles(array('ROLE_ADMIN'));
                            $user->setProfile("super_admin"); // acces à tous
                            $user->setHotel("tous");
                            // on select tous les hotels
                            $hotels = $reposHotel->findAll();
                            foreach ($hotels as $h) {
                                $user->addHotel($h);
                            }
                        } else if ($role == "admin_all_hotels") {
                            $user->setRoles(array('ROLE_ADMIN'));
                            $user->setHotel("tous_hotel");
                            $user->setProfile("admin_all_hotels"); // acces unique pour tous les hotel
                            // on select tous les hotels
                            $hotels = $reposHotel->findAll();
                            foreach ($hotels as $h) {
                                $son_nom = $h->getNom();
                                // il ne faut pas que les noms d'hôtel autre que les hôtels entre dans cette liste
                                if ($son_nom != "Tropical wood") {
                                    $user->addHotel($h);
                                }
                            }
                        } else if ($role == "editeur") {
                            $user->setRoles(array('ROLE_USER'));
                            $user->setHotel($hotel);
                            $user->setProfile("admin_hotel"); // hotel unique
                            $hotels = $reposHotel->findOneByNom($hotel);
                            $user->addHotel($hotels);
                        } else if ($role == "tropical_wood") {
                            $user->setProfile("admin_tropical_wood");
                            $user->setHotel($hotel);
                            $user->setRoles(array('ROLE_TROPICAL_WOOD'));
                            $hotels = $reposHotel->findOneByNom($hotel);
                            $user->addHotel($hotels);
                        }
                        else if ($role == "receptionniste") {
                            $user->setProfile("admin_hotel");
                            $user->setReceptionniste("oui");
                            $user->setHotel($hotel);
                            $user->setRoles(array('ROLE_USER'));
                            $hotels = $reposHotel->findOneByNom($hotel);
                            $user->addHotel($hotels);
                        }else if ($role == "comptable") {
                            $user->setProfile("admin_hotel");
                            $user->setComptable("oui");
                            $user->setHotel($hotel);
                            $user->setRoles(array('ROLE_USER'));
                            $hotels = $reposHotel->findOneByNom($hotel);
                            $user->addHotel($hotels);
                        }
                        $user->setNom($nom);
                        $user->setPrenom($prenom_user);
                        $manager->flush();
                        $retour = "ok";
                    }
                }
            }else{
                $retour = "Veuillez mentioner votre adresse email et nom ...";
            }

            $data = json_encode($retour);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response; 
        }
        
    }


    /**
     * @Route("/security/delete_user/{id}", name = "delete.user")
     */
    public function delete_user($id, Request $request, EntityManagerInterface $manager, UserRepository $repoUser)
    {
        $response = new Response();
        $user =  new User();
        if ($request->isXmlHttpRequest()) {
            $user = $repoUser->find($id);
            $manager->remove($user);
            $manager->flush();
            $data = json_encode('ok'); 
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;          
        }
    }

    /**
     * @Route("/profile/add_pdp/{id_user}", name = "edit_pdp")
     */
    public function edit_pdp($id_user, Request $request, UserRepository $repoUser, EntityManagerInterface $manager)
    {
        $response = new Response();
        $user =  new User();
        if ($request->isXmlHttpRequest()) {
            $user = $repoUser->find($id_user);
           
           if(is_array($_FILES)) {
                if(is_uploaded_file($_FILES['fichier']['tmp_name'])) {
                    $sourcePath = $_FILES['fichier']['tmp_name'];
                    $nom = $_FILES['fichier']['name'];
                    $tab_nom = explode(".",$nom);
                    $t = count($tab_nom) - 1;
                    $ext = "";
                    $tab_ext = ['png', 'jpg', 'jpeg', 'JPG', 'JPEG', 'PNG'];
                    if($t > 0){
                        $ext = $tab_nom[$t];
                    }
                    if(in_array($ext, $tab_ext)){
                        $targetPath = "uploads/" . $_FILES['fichier']['name'];
                        if (move_uploaded_file($sourcePath, $targetPath)) {
                            $user->setImage($targetPath);
                            $manager->flush();
                            $html = '
                                <div class="pdp_image" style="background-image : url({{ asset('. $targetPath .') | raw }});">
                                    <input type="file" name="fichier" id="fichier">
                                    <span class="icone_upload fa fa-plus"></span>
                                </div>
                            ';
                            $data = json_encode($html);
                            $response->headers->set('Content-Type', 'application/json');
                            $response->setContent($data);
                            return $response;
                        }
                    }
                    else{
                        $data = json_encode("error");
                        $response->headers->set('Content-Type', 'application/json');
                        $response->setContent($data);
                        return $response;
                    }

                    
                   
                }
            }
                
            
           
        }
    }

    /**
     * @Route("/profile/edit_user_compte" , name = "edit_user_compte")
     */
    public function edit_user_compte(UserPasswordEncoderInterface $passEnc, Request $request, UserRepository $repoUser, EntityManagerInterface $manager)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            
            $email = $request->request->get('email');
            $pass = $request->request->get('pass');
            $id = $request->request->get('id');
            $c_pass = $request->request->get('c_pass');
            $user = $repoUser->find($id);
            $data = "";
            if(!empty($email)){
                $user->setEmail($email);
                
                if (!empty($pass) && !empty($c_pass)) {
                    if ($c_pass == $pass) {
                        $hash = $passEnc->encodePassword($user, $pass);
                        $user->setPassword($hash);
                        $manager->flush();
                        $data = json_encode($user->getEmail());
                    } else {
                        $data = json_encode("Les mots de passes entrés ne sont pas identiques");
                    }
                }
                else{
                    $manager->flush();
                    $data = json_encode($user->getEmail());
                  
                }
            }
            else{
                if (!empty($pass) && !empty($c_pass)) {
                    if ($c_pass == $pass) {
                        $hash = $passEnc->encodePassword($user, $pass);
                        $user->setPassword($hash);
                        $manager->flush();
                        $data = json_encode($user->getEmail());
                       
                    } else {
                        $data = json_encode("Les mots de passes entrés ne sont pas identiques");
                    }
                } 
            }
            
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
            return $response;
           
        }
    }
    
}
