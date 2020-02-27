<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class PostController extends AbstractController
{
    /**
     * @var PostRepository
     */
    private $postRepository;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(PostRepository $postRepository, CategoryRepository $categoryRepository)
    {
        $this->postRepository = $postRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route("/", name="post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository): Response
    {
        $awayTeams = $this->postRepository->getByTeam(false);
        $homeTeams = $this->postRepository->getByTeam(true);

        return $this->render('post/index.html.twig', [
            'homeTeams' => $homeTeams,
            'awayTeams' => $awayTeams,
            'teams' => $this->categoryRepository->findAll(),
        ]);
    }

    /**
     * @Route("/game/{game}", name="game")
     */
    public function game($game)
    {
        $awayTeams = $this->postRepository->getByTeamAndGame(false, $game);
        $homeTeams = $this->postRepository->getByTeamAndGame(true, $game);

        return $this->render('post/game.html.twig', [
            'homeTeams' => $homeTeams,
            'awayTeams' => $awayTeams,
        ]);
    }

    /**
     * @Route("/new", name="post_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $random = (bool)rand(0,1);
            $post->setTeam($random);
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
}
