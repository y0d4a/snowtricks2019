<?php


namespace App\Controller;

use App\Repository\TricksRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @var TricksRepository
     */
    private $repository;
    /**
     * @var ObjectManager
     */
    private $em;
    /**
     * @var ImageController
     */
    private $imageController;

    public function __construct(TricksRepository $repository, ObjectManager $em, ImageController $imageController)
    {
        $this->repository = $repository;
        $this->em = $em;
        $this->imageController = $imageController;
    }

    /**
     * @Route("/", name="home")
     * @param TricksController $tricksController
     * @return Response
     */
    public function index(TricksController $tricksController)
    {
        $response = $tricksController->tricksList($currentMenu = 'home');

        return $this->render('pages/home.html.twig', $response);
    }


}