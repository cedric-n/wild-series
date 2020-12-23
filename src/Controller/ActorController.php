<?php


namespace App\Controller;

use App\Entity\Actor;
use \Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ActorController
 * @package App\Controller
 * @Route("/actor", name="actor_")
 */
class ActorController extends AbstractController
{
    /**
     * @param Actor $actor
     * @return Response
     * @Route("/{actor}", requirements={"actor"="^[0-9]+$"}, name="show")
     */
    public function show(Actor $actor): Response
    {

        return $this->render("actor/show.html.twig", [
            'actor' => $actor,
        ]);
    }
}
