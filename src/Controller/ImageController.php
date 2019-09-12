<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Tricks;
use App\Form\DefaultProfilePictureType;
use App\Form\DefaultTrickPictureType;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ImageController
 * @package App\Controller
 */
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

    /**
     * ImageController constructor.
     * @param ImageRepository $repository
     * @param ObjectManager $em
     */
    public function __construct(ImageRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/trick/edit/{id}", name="image.new", methods="GET|POST")
     * @param Tricks $trick
     * @param Request $request
     * @return RedirectResponse
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
                    $this->addFlash('danger', 'The image could not been save');
                    return $this->redirectToRoute('tricks');
                }
                $image->setName($newFilename);
                $image->setUser(($trick->getAuthor()));
                $image->setType('Trick');
            }
            $this->em->persist($image);
            $this->em->flush();
            $this->addFlash('success', 'Image has been well added');
            return $this->redirectToRoute('tricks');
        }
    }

    /**
     * @Route("/trick/edit/{id}", name="image.edit.statut", methods="EDITSTATUT")
     * @param Tricks $trick
     * @return RedirectResponse
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
        $this->addFlash('success', 'The image has been well modified');
        return $this->redirectToRoute('tricks');
    }

    /**
     * @Route("trick/edit/{id}/{i}", name="image.delete", methods="DELETE")
     * @param $id
     * @param $i
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteImage($id, $i, Request $request)
    {
        if(!empty($id) && !empty($i)){
            $image = $this->em->getRepository(Image::class)->findOneBy(['id' => $i]);
            $trick = $this->em->getRepository(Tricks::class)->findOneBy(['id' =>$id]);
            if(!empty($image) && !empty($trick) && $image->getUser() == $this->getUser() && $this->isCsrfTokenValid('image_delete' . $trick->getId(), $request->get('_token'))){
                $path = "{$this->getParameter('image_directory')}/{$image->getName()}";
                unlink($path);
                $this->em->remove($image);
                $this->em->flush();
                $this->addFlash('success', 'File well removed');
                return $this->redirectToRoute('tricks');
            }

        }
        $this->addFlash('danger', 'An error has occured, sorry for the convinience');

        return$this->redirectToRoute('tricks');
    }

    /**
     * @Route("admin/default/{type}", name="image.default")
     * @param Request $request
     * @return RedirectResponse
     */
    public function defaultProfile(Request $request, $type)
    {
        $hasAccess = $this->isGranted('ROLE_ADMIN');
        if($hasAccess){
            if($type == 'profile'){
                $form = $this->createForm(DefaultProfilePictureType::class, null);
                $form->handleRequest($request);
            }
            elseif ($type == 'trick'){
                $form = $this->createForm(DefaultTrickPictureType::class, null);
                $form->handleRequest($request);
            }
            elseif ($type !== 'profile' || $type !== 'trick'){
                $this->addFlash('danger', 'Default Type must be profile or trick');
                return $this->redirectToRoute('admin');
            }
            if($form->isSubmitted() && $form->isValid()){
                $imageName = $form['default_' . $type . '_picture']->getData();
                if($imageName){
                    $this->updateDefaultPictures($imageName, $type);
                }
                $this->addFlash('success', 'Image has been well added');
                return $this->redirectToRoute('admin');
            }
            $this->addFlash('danger', 'Form is not correct');
            return $this->redirectToRoute('admin');
        }
        $this->addFlash('danger', 'Nice try but you are not Admin');
        return $this->redirectToRoute('tricks');
    }

    /**
     * @param $imageName
     * @param $name
     * @return RedirectResponse
     */
    private function updateDefaultPictures($imageName, $name)
    {
        $this->deleteDefaultImage($name .'*');

        $originalFilename = $name;
        $newFilename = $originalFilename.'.'.$imageName->guessExtension();

        try{
            $imageName->move(
                $this->getParameter('image_directory_default'),
                $newFilename
            );
        } catch (FileException $e) {
            $this->addFlash('danger', 'The image could not been save');
            return $this->redirectToRoute('tricks');
        }
    }

    /**
     * @param $name
     * @return bool
     */
    private function deleteDefaultImage($name)
    {
        $finder = new Finder();
        $finder->files()->in("{$this->getParameter('image_directory_default')}/")->name($name);
        if($finder->hasResults()){
            foreach ($finder as $file) {
                $absoluteFilePath = $file->getRealPath();
                unlink($absoluteFilePath);
            }
        }
        return true;
    }

    /**
     * @param $name
     * @return string|null
     */
    public function getDefaultImage($name)
    {
        $finder = new Finder();
        $finder->files()->in("{$this->getParameter('image_directory_default')}/")->name($name);
        if($finder->hasResults()){
            foreach ($finder as $file) {
                $fileNameWithExtension = $file->getRelativePathname();
            }
            return $fileNameWithExtension;
        }
        return null;
    }
}