<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Tricks;
use App\Entity\User;
use App\Form\ProfilePictureType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserController
 * @package App\Controller
 */
class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $repository;

    /***
     * @var ObjectManager
     */
    private $em;

    /**
     * UserController constructor.
     * @param UserRepository $repository
     * @param ObjectManager $em
     */
    public function __construct(UserRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/user/profile/{id}", name="user.profile")
     * @param User $user
     * @return Response
     */
    public function userProfile(User $user, ImageController $imageController)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $u_id = $user->getId();
        $numberOfTricks = $this->getTotalTricks($u_id);
        $numberOfComments = $this->getTotalComments($u_id);
        $profilePicture = $imageController->getDefaultImage('profile*');

        $userArray = array(
            'user' => $user,
            'tricks' => $numberOfTricks,
            'comments' => $numberOfComments,
            'profilePicture' => $profilePicture
        );
        return $this->render('pages/userProfile.html.twig', $userArray);
    }

    /**
     * @Route("/user/edit/{id}", name="user.edit")
     * @param User $user
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     * Give access to the edit page and edit the profile
     */
    public function userEdit(User $user, UserPasswordEncoderInterface $encoder)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(ProfilePictureType::class, null, [
            'action' => $this->generateUrl('user.editPicture', ['id' => $user->getId()])
        ]);
        $formPicture = $form->createView();

        if($user == $this->getUser()){
            $editUserForm = filter_input_array(INPUT_POST);

            $u_id = $user->getId();
            $numberOfTricks = $this->getTotalTricks($u_id);
            $numberOfComments = $this->getTotalComments($u_id);

            $userArray = array(
                'id' => $user->getId(),
                'user' => $user,
                'tricks' => $numberOfTricks,
                'comments' => $numberOfComments,
                'form' => $formPicture
            );
            // check if the form has been POST and treat it
            if(!empty($editUserForm)){
                $editUser = filter_var_array($editUserForm["user"]);
                $editUserName = filter_var($editUser["username"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $editUserEmail = filter_var($editUser["email"], FILTER_SANITIZE_EMAIL);
                $oldPassword = filter_var($editUser["old_password"]);
                $newPassword = filter_var($editUser["new_password"]);
                $user->setUsername($editUserName);
                $user->setEmail($editUserEmail);

                //check if the username and email are available in DB
                $check = $this->checkUserAvailability($user);
                if($check == true){
                    if(!empty($newPassword) || !empty($oldPassword)){
                        if($encoder->isPasswordValid($user, $oldPassword) !== true){
                            $this->addFlash('danger', 'Your current password is not the same');
                            return $this->redirectToRoute('user.edit', $userArray);
                        }
                        $encoded = $encoder->encodePassword($user, $newPassword);
                        $user->setPassword($encoded);
                    }
                    $this->em->persist($user);
                    $this->em->flush();
                    $this->addFlash('success','Your information have been well updated');

                    return $this->redirectToRoute('user.edit', $userArray);
                }
                $this->addFlash('danger', 'Username or Email already used');
                return $this->redirectToRoute('user.edit', $userArray);
            } else{
                return $this->render('pages/userEdit.html.twig', $userArray);
            }
        }
        $this->addFlash('danger', 'You are not allowed to edit this user');

        $userArray = array(
            'id' => $user->getId(),
            'form' => $formPicture
        );

        return $this->redirectToRoute('user.profile', $userArray);
    }

    /**
     * @Route("/user/editPicture/{id}", name="user.editPicture")
     * @param User $user
     * @param Request $request
     * @return RedirectResponse
     */
    public function profilePicture(User $user, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if($user == $this->getUser()) {

            $u_id = $user->getId();
            $numberOfTricks = $this->getTotalTricks($u_id);
            $numberOfComments = $this->getTotalComments($u_id);
            $form0 = $this->createForm(ProfilePictureType::class, null, [
                'action' => $this->generateUrl('user.editPicture', ['id' => $user->getId()])
            ]);
            $formPicture = $form0->createView();

            $form = $this->createForm(ProfilePictureType::class, null);
            $form->handleRequest($request);

            if($form->isSubmitted()){
                $imageName = $form['profilePicture']->getData();
                if($imageName){
                    $originalFilename = pathinfo($imageName->getClientOriginalName(), PATHINFO_FILENAME);
                    $newFilename = $originalFilename.'.'.$imageName->guessExtension();
                    if($user->getProfilePicture() !== null && $newFilename !== null){
                        $path = "{$this->getParameter('image_directory_profile')}/{$user->getProfilePicture()}";
                        unlink($path);
                    }

                    try{
                        $imageName->move(
                            $this->getParameter('image_directory_profile'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $userArray = array(
                            'id' => $user->getId(),
                            'user' => $user,
                            'tricks' => $numberOfTricks,
                            'comments' => $numberOfComments,
                            'form' => $formPicture
                        );
                        $this->addFlash('danger', 'The image could not been save');
                        return $this->redirectToRoute('user.edit', $userArray);
                    }
                    $user->setProfilePicture($newFilename);
                }
                $userArray = array(
                    'id' => $user->getId(),
                    'user' => $user,
                    'tricks' => $numberOfTricks,
                    'comments' => $numberOfComments,
                    'form' => $formPicture
                );

                $this->em->persist($user);
                $this->em->flush();
                $this->addFlash('success', 'Picture well updated');
                return $this->redirectToRoute('user.edit', $userArray);
            }
            $userArray = array(
                'id' => $user->getId(),
                'user' => $user,
                'tricks' => $numberOfTricks,
                'comments' => $numberOfComments,
                'form' => $formPicture
            );
            $this->addFlash('danger', 'You got lost there');
            return $this->redirectToRoute('user.edit', $userArray);
            }

        $this->addFlash('danger', 'You are not allowed to edit this user');

        $userArray = array(
            'id' => $user->getId(),
        );

        return $this->redirectToRoute('user.profile', $userArray);
    }

    /**
     * @param $userId
     * @return int
     */
    private function getTotalTricks($userId)
    {
        $tricks = $this->getDoctrine()->getRepository(Tricks::class)->findBy(['Author' => $userId]);
        $numberOfTricks = count($tricks);

        return $numberOfTricks;
    }

    /**
     * @param $userId
     * @return int
     */
    private function getTotalComments($userId)
    {
        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(['authorId' => $userId]);
        $numberOfComments = count($comments);
        return $numberOfComments;
    }

    /**
     * @param $user
     * @return bool
     * Check if a username or email is free
     */
    private function checkUserAvailability($user)
    {
        $username = $this->repository->findOneBy(['username' => $user->getUsername()]);
        $email = $this->repository->findOneBy(['email' => $user->getEmail()]);
        if(empty($username)){
            return true;
        } elseif (empty($email)){
            return true;
        } elseif($user->getId() == $username->getId() && $user->getId() == $email->getId())
        {
            return true;
        } else{
            return false;
        }
    }
}