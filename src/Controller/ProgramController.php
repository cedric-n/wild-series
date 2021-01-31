<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\ProgramType;
use App\Form\EpisodeType;
use App\Service\Slugify;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/programs", name="program_")
 */
class ProgramController extends AbstractController
{

    /**
     * @return Response
     * @Route("/", name="index")
     */
    public function index(): Response
    {

        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        return $this->render('program/index.html.twig',
            ['programs' => $programs]
        );
    }

    /**
     * @param Request $request
     * @param Slugify $slugify
     * @return Response
     * @Route ("/new", name="new")
     */
    public function new(Request $request, Slugify $slugify): Response
    {
        $program = new Program();

        $slug = $slugify->generate($program->getTitle());
        $program->setSlug($slug);

        $form = $this->createForm(ProgramType::class, $program);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($program);

            $entityManager->flush();

            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * @param Program $program
     * @return Response
     * @Route("/{slug}",methods={"GET"}, name="show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
     */
    public function show(Program $program): Response
    {


        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : '.$program->getId().' found in program\'s table.'
            );
        }
        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy(['program' => $program->getId()]);

        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons
        ]);

    }

    /**
     * @param Program $program
     * @param Season $season
     * @return Response
     * @Route("/{slug}/seasons/{season_id}", requirements={"id"="^[0-9]+$"}, name="season_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
     * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"season_id": "id"}})
     */
    public function showSeason(Program $program,Season $season): Response
    {


        return $this->render('program/season_show.html.twig', [
            "program" => $program,
            "season" => $season,
            "episodes" => $season->getEpisodes(),
        ]);

    }

    /**
     * @param Program $program
     * @param Season $season
     * @param Episode $episode
     * @return Response
     * @Route("/{slug}/seasons/{seasonId}/episodes/{eslug}", name="episode_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
     * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"seasonId": "id"}})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"eslug": "slug"}})
     */
    public function showEpisode(Program $program, Season $season, Episode $episode): Response
    {


        return $this->render("program/episode_show.html.twig",[
            "program" => $program->getTitle(),
            "seasonNumber" => $season->getNumber(),
            "episodeNumber" => $episode->getNumber(),
            "episodeTitle" => $episode->getTitle(),
            "synopsis" => $episode->getSynopsis()
        ]);
    }

}
