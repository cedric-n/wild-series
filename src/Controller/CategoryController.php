<?php

namespace App\Controller;


use App\Entity\Category;
use App\Form\CategoryType;
use App\Entity\Program;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class CategoryController
 * @package App\Controller
 * @Route("/categories", name="category_")
 */
class CategoryController extends AbstractController
{
    /**
     * @return Response
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        return $this->render('category/index.html.twig',
            ['categories' => $categories]
        );
    }

    /**
     * @param Request $resquest
     * @return Response
     * @Route("/new", name="new")
     */
    public function new(Request $resquest): Response
    {

        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($resquest);

        if ($form->isSubmitted()) {

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($category);

            $entityManager->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * @param string $categoryName
     * @return Response
     * @Route("/show/{categoryName}",methods={"GET"}, name="show")
     */
    public function show(string $categoryName): Response
    {

        $programCategory = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy(
                [
                    'name' => $categoryName,
                ]
            );

        if (!$programCategory) {
            throw $this->createNotFoundException(
                'No category with name : '.$categoryName.' found .'
            );
        } else {
            $programs = $this->getDoctrine()
                ->getRepository(Program::class)
                ->findBy(
                    ['category' => $programCategory->getId()],
                    ['id' => 'DESC'],
                    3
                );

        }

        return $this->render('category/show.html.twig',

            [
                'programs' => $programs
            ]
        );
    }

}
