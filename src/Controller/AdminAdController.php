<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AnnonceType;
use App\Service\Pagination;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    #[Route('/admin/ads/{page<\d+>?1}', name: 'admin_ads_list')]
    public function index(AdRepository $repo, $page,Pagination $paginationService): Response
    {   
        $paginationService->setEntityClass(Ad::class)
                            ->setPage($page)
                            // ->setRoute('admin_ads_list')
                            ;

        return $this->render('admin/ad/index.html.twig',[
            'pagination'=> $paginationService
        ]);

    }

    /**
     * permet de modifier une annonce dans la partie admin
     *
     * @param Ad $ad
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/ads/{id}/edit', name: 'admin_ads_edit')]
    public function edit(Ad $ad,Request $request,EntityManagerInterface $manager)
    {
        $form = $this->createForm(AnnonceType::class,$ad);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash('success',"L'annonce a bien été modifiée");
        }

        return $this->render('admin/ad/edit.html.twig',[
            'ad'=>$ad,
            'form'=>$form->createView()
        ]);
    }

    /**
     * Suppression d'une annonce
     *
     * @param Ad $ad
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/admin/ads/{id}/delete", name: "admin_ads_delete")]
    public function delete(Ad $ad,EntityManagerInterface $manager){

        if(count($ad->getBookings()) > 0){

            $this->addFlash("warning","Vous ne pouvez pas supprimer une annonce qui possède des réservations");
        }else{

            $manager->remove($ad);
            $manager->flush();
            $this->addFlash('success',"L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée !");
        }

        return $this->redirectToRoute("admin_ads_list");

    }
}
