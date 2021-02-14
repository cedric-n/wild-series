<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\ProgramType;
use App\Service\Slugify;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * @param MailerInterface $mailer
     * @return Response
     * @throws TransportExceptionInterface
     * @Route ("/new", name="new")
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer): Response
    {

        /** @var User $user */
        $user = $this->getUser();

        $program = new Program();

        $form = $this->createForm(ProgramType::class, $program);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $program->setOwner($user);

            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($program);

            $entityManager->flush();

            $email = (new Email())
                ->from('4b3a8a02e7-03418d@inbox.mailtrap.io')
                ->to('4b3a8a02e7-03418d@inbox.mailtrap.io')
                ->subject('A new Program have been added')
                ->html($this->renderView('program/newProgramEmail.html.twig', [
                    'program' => $program
                ]));

            $mailer->send($email);

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
     * @param Request $request
     * @param Program $program
     * @return Response
     * @Route("/{slug}/edit", methods={"GET","POST"}, name="edit",)
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
     */
    public function edit(Request $request, Program $program): Response
    {
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!($this->getUser() == $program->getOwner())) {
                // If not the owner, throws a 403 Access Denied exception

                throw new AccessDeniedException('Only the owner can edit the program!');
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/edit.html.twig',[
            "program" => $program,
            "form" => $form->createView()
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
     * @param Request $request
     * @return Response
     * @Route("/{slug}/seasons/{seasonId}/episodes/{eslug}", name="episode_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
     * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"seasonId": "id"}})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"eslug": "slug"}})
     */
    public function showEpisode(Program $program, Season $season, Episode $episode, Request $request): Response
    {


        /** @var User $user */
        $user = $this->getUser();
        $comment = new Comment();
        $comment->getAuthor();
        $comment->getEpisode();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAuthor($user);
            $comment->setEpisode($episode);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('program_episode_show',[
                "slug" => $program->getSlug(),
                "seasonId" => $season->getId(),
                "eslug" => $episode->getSlug()
            ]);
        }
        $listComment = $this->getDoctrine()->getRepository(Comment::class)
            ->findBy([
                'episode' => $episode->getId(),
            ],[
                'id' => 'ASC'
            ]);

        return $this->render("program/episode_show.html.twig",[
            "program" => $program->getTitle(),
            "seasonNumber" => $season->getNumber(),
            "episodeNumber" => $episode->getNumber(),
            "episodeTitle" => $episode->getTitle(),
            "synopsis" => $episode->getSynopsis(),
            'comment' => $comment,
            'listComments' => $listComment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Program $program
     * @return Response
     * @Route("/{slug}", name="delete", methods={"DELETE"})
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
     */
    public function delete(Request $request, Program $program): Response
    {
        if ($this->isCsrfTokenValid('delete'.$program->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($program);
            $entityManager->flush();
        }

        return $this->redirectToRoute('program_index');
    }

}
