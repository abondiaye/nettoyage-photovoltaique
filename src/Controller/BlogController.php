<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog')]
    public function index(): Response
    {
        // TODO: Load projects from database
        $projects = [
            [
                'id' => 1,
                'title' => 'Installation Nettoyage Résidentielle',
                'description' => 'Nettoyage complet de panneaux solaires avec rendement +25%',
                'image_before' => '/images/projects/project1-before.jpg',
                'image_after' => '/images/projects/project1-after.jpg',
                'location' => 'Genève',
                'date' => '2026-08-10'
            ]
        ];

        return $this->render('blog/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/admin/blog', name: 'app_admin_blog')]
    public function admin(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/blog/index.html.twig');
    }

    #[Route('/admin/blog/create', name: 'app_admin_blog_create')]
    public function create(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/blog/form.html.twig');
    }
}
