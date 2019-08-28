<?php

namespace App\Controller;

use App\Entity\Tricks;
use App\Form\TricksType;
use App\Repository\TricksRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @param HomeController $homeController
     * @return Response
     */
    public function index(HomeController $homeController)

    {
        $response = $homeController->tricksList($currentMenu = 'tricks');

        return $this->render('pages/tricks.html.twig',  $response);
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
        $trick->setStatut('Publié');

        $form = $this->createForm(TricksType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $check = $this->checkTrick($trick);

            if($check == true){
                $this->em->persist($trick);
                $this->em->flush();
                $this->addFlash('success', 'Ajouté avec succès');
                return $this->redirectToRoute('tricks');
            }
            $this->addFlash('danger', 'Ce nom de trick existe déjà');

            return $this->redirectToRoute('tricks', ['request' => $request], 307);
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
        $trick->setStatut('Edité');

        $form = $this->createForm(TricksType::class, $trick);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $check = $this->checkTrick($trick);

            if($check == true){
                $this->em->persist($trick);
                $this->em->flush();
                $this->addFlash('success', 'Edité avec succès');
                return $this->redirectToRoute('tricks');
            }
            $this->addFlash('danger', 'Ce nom de trick existe déjà');
            return $this->redirectToRoute('tricks', ['request' => $form], 307);
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

    private function checkTrick($trick)
    {
        $check = $this->repository->findOneBy(['title' => $trick->getTitle()]);
        if(empty($check)){
            $result = true;
        } elseif(!empty($trick->getId()) && $trick->getId() == $check->getId()){
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

}
