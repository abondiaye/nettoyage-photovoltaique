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
    public function index(
        ReservationService $reservationService,
        ReservationRepository $reservationRepository,
        NotificationRepository $notificationRepository
    ): Response {
        // Statistiques globales
        $stats = $reservationService->getDashboardStats();

        // Demandes en attente
        $pendingReservations = $reservationRepository->findByStatut('EN_ATTENTE');

        // Demandes confirmées
        $confirmedReservations = $reservationRepository->findByStatut('CONFIRMEE');

        // Notifications non lues
        $unreadNotifications = $notificationRepository->findUnread();
        $unreadCount = count($unreadNotifications);

        // Interventions à venir
        $upcomingInterventions = $reservationRepository->findBySearchCriteria([]);

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'pendingReservations' => $pendingReservations,
            'confirmedReservations' => $confirmedReservations,
            'unreadNotifications' => $unreadNotifications,
            'unreadCount' => $unreadCount,
        ]);
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
