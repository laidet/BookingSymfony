<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;
use Doctrine\ORM\EntityManager;
use App\Repository\AdRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdController extends AbstractController
{
    // Permet d'afficher une liste d'annonces donc plusieurs annonces
    #[Route('/ads', name: 'ads_list')]

    // cours de stéphanie mais fonctionne plus
        // public function index(){
            // $repo = $this->getDoctrine()->getRepository(Ad::class);
            // $ads = $repo->findAll();
        //}
    
        public function index(AdRepository $repo): Response
        {
            $ads = $repo->findAll();
        
        return $this->render('ad/index.html.twig', [
            'controller_name' => 'Nos annonces',
            'ads' => $ads
            ]);
        }

     // Permet de créer une annonce
     #[Route('/ads/new', name: 'ads_create')]

     public function create(Request $request,EntityManagerInterface $manager) : Response
     {
        // fabricant de formulaire de symfony : FORMBUILDER
        $ad = new Ad();

        // on lance la fabrication et la configuration de notre formulaire
        $form = $this->createForm(AnnonceType::class,$ad);

        // récupération des données du formulaire qui sont stocké dans $ad
        $form -> handleRequest($request);

        // on vérifie si le formulaire est soumis et validé
        if($form->isSubmitted() && $form->isValid()){

            // pour chaque image supplémentaire ajoutée
            foreach($ad->getImages() as $image){

                // on relie l'image à l'annonce et on modifie l'annonce
                $image->setAd($ad);

                // on sauvegarde les images
                $manager->persist($image);
            }

            // si le formulaire est soumis ET si le formulaire est valide, on demande a Doctrine de sauvegarder ces données dans l'objet $manager
            $manager->persist($ad);
            $manager->flush();

            // pour afficher un lessage flash
            $this->addFlash('success',"Annonce <strong>{$ad->getTitle()}</strong> créée avec succès");

            // on fait une redirection après validation vers l'annonce qui à été crée
            return $this->redirectToRoute('ads_single',['slug'=>$ad->getSlug()]);
        }
 
        return $this->render('ad/new.html.twig',['form'=>$form->createView()]);
     }


    // Permet d'afficher une seule annonce
    #[Route('/ads/{slug}', name: 'ads_single')]

    public function show($slug,Ad $ad) : Response
    {
        // je récupère l'annonce qui correspond au slug
        // X = 1 champ de la table, à préciser à la place de X
        // finByX = renvoi un tableau d'annonces (plusieurs éléments)
        // findOneByX = renvoi un élément
           // $ad = $repo->findOneBySlug($slug);

            return $this->render('ad/show.html.twig',['ad'=>$ad]);
    }

    /**
     * Permet d'éditer et de modifier un article
     * @Route("/ads/{slug}/edit", name="ads_edit")
     *
     * @return Response
     */
    public function edit(Ad $ad,Request $request,EntityManagerInterface $manager){

        $form = $this->createForm(AnnonceType::class,$ad);
        $form -> handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            foreach($ad->getImages() as $image){

                $image->setAd($ad);

                $manager->persist($image);
            }

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash("success","Les modifications ont été faites !");

            return $this->redirectToRoute('ads_single',['slug'=>$ad->getSlug()]);
        }

        return $this->render('ad/edit.html.twig',['form'=>$form->createView(),'ad'=>$ad]);
    }
}
