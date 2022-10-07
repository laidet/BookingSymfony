<?php


// namespace : chemlin du controller
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    

// Pour créer une page on a besoin : - d'une fonction publique (classe)
//                                   - d'une route
//                                   - d'une réponse

class HomeController extends AbstractController{

    /**
     * Création de notre 1ère route
     * @Route("/", name="homepage")
     * 
     */

    public function home(){

        $noms = ['Durand'=>'visiteur','François'=>'admin','Dupont'=>'contributeur'];
        return $this->render('home.html.twig',['titre'=>'Site d\'annonces !','acces'=>'visiteur','tableau'=>$noms]);

    }

    /**
     * Montre la page qui salut l'utilisateur
     * 
     * @Route("/hello/{nom}",name="hello-utilisateur")
     * @Route("/profil",name="hello-base")
     * @Route("/profil/{nom}/acces/{acces}", name="hello-profil")
     * @return void
     */

    public function hello($nom="anonyme",$acces="visiteur"){

        return $this->render('hello.html.twig',['title'=>'Page de profil','nom'=>$nom,'acces'=>$acces]);
    }
}