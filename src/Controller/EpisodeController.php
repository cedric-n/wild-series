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
     * @Route("new", name="new")
     */
    public function new(Request $request, Slugify $slugify):Response
    {
        $episode = new Episode();




        $form = $this->createForm(EpisodeType::class, $episode);

        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $slug = $slugify->generate($episode->getTitle());

            $episode->setSlug($slug);

            $entityManager->persist($episode);

            $entityManager->flush();

            return $this->redirectToRoute('program_episode_show', ["slug" => $episode->getSeason()->getProgram()->getSlug(), "seasonId" => $episode->getSeason()->getNumber(), "eslug" => $episode->getSlug()]);
        }

        return $this->render('episode/new.html.twig',[
            'form' => $form->createView()
        ]);
    }

}
