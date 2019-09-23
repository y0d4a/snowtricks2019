<?php

namespace App\Controller;

use App\Entity\Tricks;
use App\Entity\Videos;
use App\Form\VideosType;
use App\Repository\VideosRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VideosController extends AbstractController
{
    /**
     * @var VideosRepository
     */
    private $repository;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * VideosController constructor.
     * @param VideosRepository $repository
     * @param ObjectManager $em
     */
    public function __construct(VideosRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }
    
    /**
     * @Route("/videos/add/{id}", name="videos.add")
     * @param Tricks $trick
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function addVideos(Tricks $trick, Request $request, $id)
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $video = new Videos();
        $video->setTricks($trick);
        $form = $this->createForm(VideosType::class, $video);
        $form->handleRequest($request);

        if(is_numeric($id) && $trick->getAuthor() == $this->getUser() && $form->isSubmitted() && $form->isValid()){
            $this->em->persist($video);
            $this->em->flush();
            $this->addFlash('success', 'Video correctly update');
            return $this->redirectToRoute('tricks');
        }
        $errors = $form['url']->getErrors();
        if(count($errors)){
            foreach ($errors as $error){
                $this->addFlash('danger', $error->getMessage());
            }
        }
        return $this->redirectToRoute('tricks');
    }
}