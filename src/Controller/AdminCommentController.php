<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Service\Pagination;
use App\Form\AdminCommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentController extends AbstractController
{
    #[Route('/admin/comments/{page<\d+>?1}', name: 'admin_comments_list')]
    public function index(CommentRepository $repo,$page,Pagination $paginationService): Response
    {
        $paginationService->setEntityClass(Comment::class)
                            ->setLimit(5)
                            ->setPage($page)
                            // ->setRoute('admin_comments_list')
                            ;

        return $this->render('admin/comment/index.html.twig', [
            'pagination' => $paginationService
        ]);
    }

    /**
     * Permet d'éditer un commentaire via l'admin
     *
     * @param Comment $comment
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/comment/{id}/edit', name: 'admin_comment_edit')]
    public function edit(Comment $comment,Request $request,EntityManagerInterface $manager){

        $form = $this->createForm(AdminCommentType::class,$comment);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){

            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success','Le commentaire a été enregistré');
            return $this->redirectToRoute('admin_comments_list');
        }

        return $this->render('admin/comment/edit.html.twig',[
                'comments'=>$comment,
                'form'=>$form->createView()
        ]);
    }

    /**
     * Suppression d'un commentaire
     *
     * @param Comment $comment
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/admin/comment/{id}/delete', name: 'admin_comment_delete')]
    public function delete(Comment $comment,EntityManagerInterface $manager){

        $manager->remove($comment);
        $manager->flush();
        $this->addFlash('success',"Le commentaire{$comment->getId()} a bien été supprimé !");

        return $this->redirectToRoute('admin_comments_list');
    }
}
