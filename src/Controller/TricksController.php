<?php

namespace App\Controller;

use App\Entity\Tricks;
use App\Form\TricksType;
use App\Repository\TricksRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
    public function index()
    {
        $tricks = $this->repository->findBy(
            ['statut' => ['Publié', 'Edité']],
            ['dateUpdate' => 'DESC']
        );
        return $this->render('pages/tricks.html.twig', array('tricks' => $tricks, 'current_menu' => 'tricks'));
    }

    /**
     * @Route("/trick/{id}", name="trick")
     */
    public function trick($id)
    {
        $trick = $this->repository->findOneBy(
            ['id' => $id ]
        );

        $encoder = [new JsonEncoder()];
        $normalizer = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizer,$encoder);

        $response = $serializer->serialize($trick, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new Response($response, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/tricks/new", name="tricks.new")
     */
    public function tricksNew(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $tricks = new Tricks();
        $tricks->setDatePost(new \DateTime('now'));
        $tricks->setAuthor($this->getUser());
        $tricks->setEditor($this->getUser());

        $form = $this->createForm(TricksType::class, $tricks);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($tricks);
            $this->em->flush();
            return $this->redirectToRoute('tricks');
        }

        return $this->render('pages/tricksNew.html.twig', array(
            'current_menu' => 'tricks',
            'tricks' => $tricks,
            'form' => $form->createView()
        ));
    }


}
