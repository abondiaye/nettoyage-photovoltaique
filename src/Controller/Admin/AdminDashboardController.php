<?php

namespace App\Controller\Admin;

use App\Repository\ReservationRepository;
use App\Repository\NotificationRepository;
use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        // The old dashboard (links that went nowhere) is replaced by /admin/dashboard.
        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/notifications', name: 'notifications')]
    public function notifications(NotificationRepository $notificationRepository): Response
    {
        $notifications = $notificationRepository->findBy([], ['dateCreation' => 'DESC']);

        return $this->render('admin/notifications/list.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/notification/{id}/read', name: 'notification_read', methods: ['POST'])]
    public function markNotificationRead(
        int $id,
        NotificationRepository $notificationRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager
    ): Response {
        $notification = $notificationRepository->find($id);

        if (!$notification) {
            throw $this->createNotFoundException('Notification non trouvée');
        }

        $notification->setLue(true);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/reservation/{id}/details', name: 'reservation_details')]
    public function reservationDetails(
        int $id,
        ReservationRepository $reservationRepository
    ): Response {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        return $this->render('admin/reservations/details.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}
