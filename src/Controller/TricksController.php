<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Tricks;
use App\Form\CommentType;
use App\Form\TricksType;
use App\Repository\TricksRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class TricksController extends AbstractController
{
    /**
     * @var TricksRepository
     */
    private $repository;

    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(TricksRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }
    /**
     * @Route("/tricks", name="tricks")
     */
    public function index(Request $request)
    {
        $comments = array();

        $tricks = $this->repository->findBy(
            ['statut' => ['Publié', 'Edité']],
            ['dateUpdate' => 'DESC']
        );

        foreach ($tricks as $trick){
            $form = $this->createForm(TricksType::class, $trick, [
                'action' => $this->generateUrl('trick.edit', ['id' => $trick->getId()])
            ]);
            $forms[] = $form->createView();

            /* comment form */
            $newComment = new Comment();
            $formNewComment = $this->createForm(CommentType::class, $newComment, [
                'action' => $this->generateUrl('comment.new', ['id' => $trick->getId()])
            ]);
            $formsNewComment[$trick->getId()] = $formNewComment->createView();

            /* Comment view */
            $comments[$trick->getId()][] = $this->em->getRepository(Comment::class)->findBy(
                ['tricksId' => $trick->getId()]
            );
        }

        /* trick form */
        $newTrick = new Tricks();
        $formNew = $this->createForm(TricksType::class, $newTrick, [
            'action' => $this->generateUrl('tricks.new')
        ]);

        return $this->render('pages/tricks.html.twig', array(
            'tricks' => $tricks,
            'current_menu' => 'tricks',
            'newTrick' => $newTrick,
            'formNew' => $formNew->createView(),
            'formEdit' => $forms,
            'formsNewComment' => $formsNewComment,
            'comments' => $comments
            ));
    }

    /**
     * @Route("/tricks/new", name="tricks.new")
     */
    public function tricksNew(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $trick = new Tricks();
        $trick->setDatePost(new \DateTime('now'));
        $trick->setDateUpdate(new \DateTime('now'));
        $trick->setAuthor($this->getUser());
        $trick->setEditor($this->getUser());

        $form = $this->createForm(TricksType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($trick);
            $this->em->flush();
            $this->addFlash('success', 'Ajouté avec succès');
            return $this->redirectToRoute('tricks');
        }

        return $this->render('pages/tricks.html.twig', array(
            'current_menu' => 'tricks',
            'tricks' => $trick,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/tricks/edit/{id}", name="trick.edit", methods="GET|POST")
     * @param Tricks $trick
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function trickEdit(Tricks $trick, Request $request)
    {
        $trick->setDateUpdate(new \DateTime('now'));
        $trick->setEditor($this->getUser());

        $form = $this->createForm(TricksType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($trick);
            $this->em->flush();
            $this->addFlash('success', 'Edité avec succès');
            return $this->redirectToRoute('tricks');
        }
    }

    /**
     * @Route("/tricks/edit/{id}", name="trick.delete", methods="DELETE")
     * @param Tricks $trick
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Tricks $trick, Request $request)
    {
        if ($this->getUser() == $trick->getAuthor() && $this->isCsrfTokenValid('delete' . $trick->getId(), $request->get('_token'))){
            $this->em->remove($trick);
            $this->em->flush();
            $this->addFlash('success', 'Supprimé avec succès');
            return $this->redirectToRoute('tricks');
        }
    }

}
