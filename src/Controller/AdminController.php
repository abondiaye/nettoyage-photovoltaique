<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use App\Repository\CommentRepository;
use App\Repository\PointRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(): Response {
        return $this->render('admin/dashboard.html.twig', [
            'project_count' => 0,
            'user_count' => 0,
            'quote_count' => 0,
        ]);
    }

    #[Route('/appointments', name: 'app_admin_appointments')]
    public function appointments(AppointmentRepository $appointmentRepo): Response
    {
        $appointments = $appointmentRepo->findAll();

        return $this->render('admin/appointments.html.twig', [
            'appointments' => $appointments,
        ]);
    }

    #[Route('/members', name: 'app_admin_members')]
    public function members(UserRepository $userRepo): Response
    {
        $members = $userRepo->findByRole('ROLE_MEMBER');

        return $this->render('admin/members.html.twig', [
            'members' => $members,
        ]);
    }

    #[Route('/comments', name: 'app_admin_comments')]
    public function comments(CommentRepository $commentRepo): Response
    {
        $comments = $commentRepo->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/comments.html.twig', [
            'comments' => $comments,
        ]);
    }
}
