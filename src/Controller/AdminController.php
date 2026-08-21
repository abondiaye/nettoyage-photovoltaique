<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use App\Repository\CommentRepository;
use App\Repository\MessageRepository;
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
    public function dashboard(
        AppointmentRepository $appointmentRepo,
        MessageRepository $messageRepo
    ): Response {
        $totalAppointments = $appointmentRepo->count([]);
        $pendingAppointments = $appointmentRepo->count(['status' => 'pending']);
        $confirmedAppointments = $appointmentRepo->count(['status' => 'confirmed']);
        $proposedAppointments = $appointmentRepo->count(['status' => 'proposed']);
        $refusedAppointments = $appointmentRepo->count(['status' => 'refused']);

        $totalMessages = $messageRepo->count([]);
        $recentMessages = $messageRepo->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'total_appointments' => $totalAppointments,
            'pending_appointments' => $pendingAppointments,
            'confirmed_appointments' => $confirmedAppointments,
            'proposed_appointments' => $proposedAppointments,
            'refused_appointments' => $refusedAppointments,
            'total_messages' => $totalMessages,
            'recent_messages' => $recentMessages,
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
