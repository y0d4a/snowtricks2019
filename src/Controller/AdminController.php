<?php

namespace App\Controller;

use App\Form\DefaultProfilePictureType;
use App\Form\DefaultTrickPictureType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     * @param ImageController $imageController
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index(ImageController $imageController)
    {
        $hasAccess = $this->isGranted('ROLE_ADMIN');
        if($hasAccess){
            $formProfile = $this->createForm(DefaultProfilePictureType::class, null, [
                'action' => $this->generateUrl('image.default', ['type' => 'profile'])
            ]);
            $formTrick = $this->createForm(DefaultTrickPictureType::class, null, [
                'action' => $this->generateUrl('image.default', ['type' => 'trick'])
            ]);

            $profilePicture = $imageController->getDefaultImage('profile*');
            $trickPicture = $imageController->getDefaultImage('trick*');

            $array = [
                'current_menu' => 'admin',
                'formProfile' => $formProfile->createView(),
                'formTrick' => $formTrick->createView(),
                'profilePicture' => $profilePicture,
                'trickPicture' => $trickPicture
                ];
            return $this->render('pages/admin.html.twig', $array);
        }
        $this->addFlash('danger', 'Nice try but you are not Admin');
        return $this->redirectToRoute('tricks');
    }
}