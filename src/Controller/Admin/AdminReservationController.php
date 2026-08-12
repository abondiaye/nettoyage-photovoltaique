<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use App\Entity\Customer;
use App\Repository\ReservationRepository;
use App\Repository\CustomerRepository;
use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

#[Route('/admin/reservations', name: 'admin_reservations_')]
#[IsGranted('ROLE_ADMIN')]
class AdminReservationController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(ReservationRepository $reservationRepository, Request $request): Response
    {
        $statut = $request->query->get('statut');
        $page = $request->query->getInt('page', 1);
        $limit = 20;

        if ($statut) {
            $reservations = $reservationRepository->findByStatut($statut);
        } else {
            $reservations = $reservationRepository->findAll();
        }

        return $this->render('admin/reservations/list.html.twig', [
            'reservations' => $reservations,
            'currentStatut' => $statut,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(
        int $id,
        ReservationRepository $reservationRepository
    ): Response {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        return $this->render('admin/reservations/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/confirm', name: 'confirm', methods: ['POST'])]
    public function confirm(
        int $id,
        ReservationRepository $reservationRepository,
        ReservationService $reservationService
    ): Response {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        try {
            $adminName = $this->getUser()?->getUserIdentifier() ?? 'Admin';
            $reservationService->validateReservation($reservation, $adminName);

            $this->addFlash('success', 'Réservation confirmée avec succès');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_reservations_show', ['id' => $id]);
    }

    #[Route('/{id}/refuse', name: 'refuse', methods: ['POST'])]
    public function refuse(
        int $id,
        Request $request,
        ReservationRepository $reservationRepository,
        ReservationService $reservationService
    ): Response {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        $raison = $request->request->get('raison', 'Raison non spécifiée');

        try {
            $adminName = $this->getUser()?->getUserIdentifier() ?? 'Admin';
            $reservationService->refuseReservation($reservation, $raison, $adminName);

            $this->addFlash('success', 'Réservation refusée');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_reservations_show', ['id' => $id]);
    }

    #[Route('/{id}/reschedule', name: 'reschedule', methods: ['POST'])]
    public function reschedule(
        int $id,
        Request $request,
        ReservationRepository $reservationRepository,
        ReservationService $reservationService
    ): Response {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        $newDate = $request->request->get('newDate');
        $newTime = $request->request->get('newTime');

        if (!$newDate || !$newTime) {
            $this->addFlash('error', 'Date et heure sont obligatoires');
            return $this->redirectToRoute('admin_reservations_show', ['id' => $id]);
        }

        try {
            $dateObj = DateTime::createFromFormat('Y-m-d', $newDate);
            $timeObj = DateTime::createFromFormat('H:i', $newTime);

            $adminName = $this->getUser()?->getUserIdentifier() ?? 'Admin';
            $reservationService->rescheduleReservation($reservation, $dateObj, $timeObj, $adminName);

            $this->addFlash('success', 'Intervention reportée');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_reservations_show', ['id' => $id]);
    }

    #[Route('/{id}/cancel', name: 'cancel', methods: ['POST'])]
    public function cancel(
        int $id,
        Request $request,
        ReservationRepository $reservationRepository,
        ReservationService $reservationService
    ): Response {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        $motif = $request->request->get('motif', 'Motif non spécifié');

        try {
            $adminName = $this->getUser()?->getUserIdentifier() ?? 'Admin';
            $reservationService->cancelReservation($reservation, $motif, $adminName);

            $this->addFlash('success', 'Intervention annulée');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_reservations_show', ['id' => $id]);
    }

    #[Route('/{id}/complete', name: 'complete', methods: ['POST'])]
    public function complete(
        int $id,
        Request $request,
        ReservationRepository $reservationRepository,
        ReservationService $reservationService
    ): Response {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        $prixRealise = $request->request->get('prixRealise');
        $commentaire = $request->request->get('commentaire');

        try {
            $adminName = $this->getUser()?->getUserIdentifier() ?? 'Admin';
            $reservationService->completeReservation($reservation, floatval($prixRealise), $commentaire, $adminName);

            $this->addFlash('success', 'Intervention marquée comme réalisée');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_reservations_show', ['id' => $id]);
    }
}
