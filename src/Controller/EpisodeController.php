<?php


namespace App\Controller;

use App\Entity\Episode;
use App\Form\EpisodeType;
use App\Service\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class EpisodeController
 * @package App\Controller
 * @Route("episodes/", name="episode_" )
 */
class EpisodeController extends AbstractController
{

    /**
     * @param Request $request
     * @param Slugify $slugify
     * @return Response
     * @Route("/new/episode", name="new")
     */
    public function newEpisode(Request $request, Slugify $slugify):Response
    {
        $episode = new Episode();

        $slug = $slugify->generate($episode->getTitle());

        $episode->setSlug($slug);

        $form = $this->createForm(EpisodeType::class, $episode);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($episode);

            $entityManager->flush();

            return $this->redirectToRoute('program_episode_show');
        }

        return $this->render('episode/newEp.html.twig',[
            'form' => $form->createView()
        ]);
    }



}
