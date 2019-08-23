<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Tricks;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @var CommentRepository
     */
    private $repository;

    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(CommentRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/comment/{id}/new", name="comment.new")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function commentNew(Tricks $trick, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $comment = new Comment();
        $comment->setPublishedat(new \DateTime('now'));
        $comment->setEditedAt(new \DateTime('now'));
        $comment->setAuthorId($this->getUser());
        $comment->setTricksId($trick);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($comment);
            $this->em->flush();
            $this->addFlash('success', 'Ajouté avec succès');
            return $this->redirectToRoute('tricks');
        }

        return $this->render('pages/tricks.html.twig', array(
            'current_menu' => 'tricks',
            'form' => $form->createView()
        ));
    }
}