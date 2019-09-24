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
            $url = $video->getUrl();
            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $youtubeMatch);
            preg_match('%(?:https?://)?(?:www\.)?(?:dai\.ly/|dailymotion\.com(?:/video/|/embed/|/embed/video/))([^^&?/ ]{7})%i', $url, $dailymotionMatch);

            if(!empty($youtubeMatch) || !empty($dailymotionMatch)){
                if(!empty($youtubeMatch)){
                    $newUrl = "https://www.youtube.com/embed/$youtubeMatch[1]";
                }elseif (!empty($dailymotionMatch)){
                    $newUrl = "https://www.dailymotion.com/embed/video/$dailymotionMatch[1]";
                }

                $video->setUrl($newUrl);
                $this->em->persist($video);
                $this->em->flush();
                $this->addFlash('success', 'Video correctly update');
                return $this->redirectToRoute('tricks');
            }
            $this->addFlash('danger', 'URL must be from Youtube or DailyMotion');
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

    /**
     * @param Videos $video
     * @param Request $request
     * @Route("/video/delete/{id}", name="video.delete")
     * @return RedirectResponse
     */
    public function deleteVideo(Videos $video, Request $request)
    {
        if($this->getUser() == $video->getTricks()->getAuthor() && $this->isCsrfTokenValid('video_delete' . $video->getTricks()->getId(), $request->get('_token'))){
            $this->em->remove($video);
            $this->em->flush();
            $this->addFlash('success', 'Video has been well deleted');
            return $this->redirectToRoute('tricks');
        }
        $this->addFlash('danger', 'You are not allowed to delete this video');
        return $this->redirectToRoute('tricks');
    }
}