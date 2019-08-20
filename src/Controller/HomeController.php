<?php


namespace App\Controller;


use App\Repository\TricksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @var TricksRepository
     */
    private $repository;

    public function __construct(TricksRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * @Route("/", name="home")
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index():Response
    {
        $tricks = $this->repository->findBy(
            ['statut' => ['Publié', 'Edité']],
            ['dateUpdate' => 'DESC']
        );
        return $this->render('pages/home.html.twig', array('tricks' => $tricks, 'current_menu' => 'home'));
    }
}