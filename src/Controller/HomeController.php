<?php


namespace App\Controller;


use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\TricksType;
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

    public function __construct(TricksRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
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

        return $this->render('pages/home.html.twig', array(
            'tricks' => $tricks,
            'current_menu' => 'home',
            'formEdit' => $forms,
            'formsNewComment' => $formsNewComment,
            'comments' => $comments
        ));
    }
}