<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Tricks;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    /**
     * @var ImageRepository
     */
    private $repository;

    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(ImageRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/trick/edit/{id}", name="image.new", methods="GET|POST")
     */
    public function newImage(Tricks $trick, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $image = new Image();
        $image->setTrick($trick);
        if(empty($image->getStatut())){
            $image->setStatut('secondary');
        }

        $form = $this->createForm(ImageType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $imageName = $form['name']->getData();
            if($imageName){
                $originalFilename = pathinfo($imageName->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageName->guessExtension();

                try{
                    $imageName->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('danger', 'L\'image n\'a pas pu être sauvée');
                    return $this->redirectToRoute('tricks');
                }
                $image->setName($newFilename);
            }
            $this->em->persist($image);
            $this->em->flush();
            $this->addFlash('success', 'L\'image a bien été sauvegardée');
            return $this->redirectToRoute('tricks');
        }
    }

    /**
     * @Route("/trick/edit/{id}", name="image.edit.statut", methods="EDITSTATUT")
     * @param Tricks $trick
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editImageStatut(Tricks $trick)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $post = filter_input_array(INPUT_POST);
        $imageId = filter_var($post["image"]["id"], FILTER_SANITIZE_NUMBER_INT);

        $images[$trick->getId()][] = $this->em->getRepository(Image::class)->findBy(
            ['trick' => $trick->getId()]
        );

        foreach($images as $image){
            foreach($image as $img){
                foreach($img as $i){
                    if($i->getId() == $imageId){
                        $i->setStatut('primary');
                    } else{
                        $i->setStatut('secondary');
                    }
                    $this->em->persist($i);
                }
            }
        }
        $this->em->flush();
        $this->addFlash('success', 'L\'image a bien été modifiée');
        return $this->redirectToRoute('tricks');
    }
}