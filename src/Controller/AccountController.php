<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountController extends AbstractController
{
    // Permet d'afficher une page connexion
    #[Route('/login', name: 'app_account_login')]


    public function login(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError(); // pour gérer d'éventuel erreur d'authentification
        $username = $utils->getLastUsername(); // pour stocker le dernière personne connecté
        return $this->render('account/login.html.twig',[
            'hasError'=>$error!==null, // 1er paramètre s'il y a une erreur 
            'username'=>$username,

        ]);
    }

    /**
     * Permet de se déconnecter
     * @Route("/logout",name="app_account_logout")
     * 
     * @return void
     */
    public function logout(){
        // besoin de rien tout se passe via le fichier security.yaml
    }

    /**
     *  Permet d'afficher une page s'inscrire
     * @Route("/register",name="account_register")
     * 
     * @return Response
     */
    public function register(Request $request, UserPasswordHasherInterface $encoder, EntityManagerInterface $manager){

        $user = new User();

        $form = $this->createForm(RegistrationType::class,$user);

        $form->handleRequest($request); // requete

        if($form->isSubmitted() && $form->isValid()){ // si le formulaire est soumis et valide

            // On prend le mot de passe inscrit dans le formulaire
            $hash = $encoder->hashPassword($user,$user->getHash());

            // On modifie le mot de passe avec le setter
            $user->setHash($hash);

            $manager->persist($user);
            $manager->flush();

            // message flash
            $this->addFlash("success","Votre compte a bien été crée");

            return $this->redirectToRoute("app_account_login");

        }

        return $this->render("account/register.html.twig",[
            'form'=>$form->createView()
        ]);
    }

    /**
     * Modification du profil utilisateur
     *
     * @Route("/account/profile",name="account_profile")
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     */
    public function profile(Request $request, EntityManagerInterface $manager){

        $user = $this->getUser();

        $form = $this->createForm(AccountType::class,$user);
        $form->handlerequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $manager->persist($user);
            $manager->flush();
            $this->addFlash("success","Les informations de votre profil ont bien été modifiées.");
        }

        return $this->render('account/profile.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * Permet la modification du mot de passe
     * @Route("/account/password-update",name="account_password")
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     * 
     */
    public function updatePassword(Request $request,UserPasswordHasherInterface $encoder, EntityManagerInterface $manager){

        $passwordUpdate = new PasswordUpdate();
        $user=$this->getUser(); // pour aller chercher l'utilisateur connecté 

        $form=$this->createForm(PasswordUpdateType::class,$passwordUpdate);

        $form->handleRequest($request);

        // sauvegarder le nouveau mot de passe dans la bdd
        if($form->isSubmitted() && $form->isValid()){

            // si le mot de passe n'est pas le bon
            if(!password_verify($passwordUpdate->getOldPassword(),$user->getHash())){

                // message d'erreur
                // $this->addFlash("warning","Mot de passe actuel est incorrect");
                $form->get('OldPassword')->addError(new FormError("Le mot de passe que vous avez entrez n'est pas votre mot de passe actuel"));

            }else{

            // On récupère le nouveau mot de passe
            $newPassword = $passwordUpdate->getNewPassword();

            // on crypte le nouveau mot de passe
            $hash = $encoder->hashPassword($user,$newPassword);

            // on enregistre le nouveau mot de passe dans le setter
            $user->setHash($hash);

            // on enregistre
            $manager->persist($user);
            $manager->flush();

            // on ajoute un message
            $this->addFlash("success","Votre nouveau mot de passe a bien été enregistré");

            // on redirige
            return $this->redirectToRoute('account_profile');

            }
        }

        return $this->render('account/password.html.twig',[
            'form'=>$form->createView()
        ]);

    }

        /**
         * Permet d'afficher la page mon compte
         * @Route("/account",name="account_home")
         * @IsGranted("ROLE_USER")
         * 
         * @return Response
         */
        public function myAccount(){

            return $this->render("user/index.html.twig",['user'=>$this->getUser()]);
            
        }
}
