<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\ReservationHistory;
use App\Entity\Notification;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class ReservationService
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private EntityManagerInterface $entityManager,
        private NotificationService $notificationService,
        private EmailService $emailService
    ) {}

    /**
     * Valider une réservation en attente
     */
    public function validateReservation(Reservation $reservation, string $administrateur): void
    {
        if ($reservation->getStatut() !== 'EN_ATTENTE') {
            throw new \Exception('Seules les demandes en attente peuvent être validées');
        }

        $reservation->setStatut('CONFIRMEE');
        $this->addHistory($reservation, 'VALIDATION', 'EN_ATTENTE', 'CONFIRMEE', $administrateur);

        $this->entityManager->flush();

        // Créer une notification
        $this->notificationService->createNotification(
            'Réservation confirmée',
            'Votre intervention de nettoyage a été confirmée pour le ' . $reservation->getDateSouhaitee()->format('d/m/Y'),
            'confirmation',
            $reservation
        );

        // Envoyer email au client
        $this->emailService->sendConfirmationEmail($reservation);
    }

    /**
     * Refuser une réservation
     */
    public function refuseReservation(Reservation $reservation, string $raison, string $administrateur): void
    {
        if ($reservation->getStatut() !== 'EN_ATTENTE') {
            throw new \Exception('Seules les demandes en attente peuvent être refusées');
        }

        $reservation->setStatut('REFUSEE');
        $this->addHistory($reservation, 'REFUS', 'EN_ATTENTE', 'REFUSEE', $administrateur);

        $this->entityManager->flush();

        // Notification
        $this->notificationService->createNotification(
            'Réservation refusée',
            'Malheureusement, votre demande de nettoyage a été refusée. Raison: ' . $raison,
            'refusal',
            $reservation
        );

        // Email au client
        $this->emailService->sendRefusalEmail($reservation, $raison);
    }

    /**
     * Reporter une intervention à une nouvelle date
     */
    public function rescheduleReservation(Reservation $reservation, DateTime $newDate, DateTime $newTime, string $administrateur): void
    {
        $oldDate = $reservation->getDateSouhaitee();
        $reservation->setDateSouhaitee($newDate);
        $reservation->setHeureSouhaitee($newTime);

        $this->addHistory(
            $reservation,
            'REPORT',
            $oldDate->format('d/m/Y H:i'),
            $newDate->format('d/m/Y H:i'),
            $administrateur
        );

        $this->entityManager->flush();

        // Notification
        $this->notificationService->createNotification(
            'Intervention reportée',
            'Votre intervention a été reportée au ' . $newDate->format('d/m/Y à H:i'),
            'reschedule',
            $reservation
        );

        // Email
        $this->emailService->sendRescheduleEmail($reservation);
    }

    /**
     * Annuler une intervention confirmée
     */
    public function cancelReservation(Reservation $reservation, string $motif, string $administrateur): void
    {
        if (!in_array($reservation->getStatut(), ['EN_ATTENTE', 'CONFIRMEE', 'A_REPLANIFIER'])) {
            throw new \Exception('Cette réservation ne peut pas être annulée');
        }

        $oldStatut = $reservation->getStatut();
        $reservation->setStatut('ANNULEE_ADMIN');

        $this->addHistory(
            $reservation,
            'ANNULATION_ADMIN',
            $oldStatut,
            'ANNULEE_ADMIN',
            $administrateur
        );

        $this->entityManager->flush();

        // Notification
        $this->notificationService->createNotification(
            'Intervention annulée',
            'Votre intervention a été annulée. Motif: ' . $motif,
            'cancellation',
            $reservation
        );

        // Email
        $this->emailService->sendCancellationEmail($reservation, $motif);
    }

    /**
     * Marquer une intervention comme réalisée
     */
    public function completeReservation(Reservation $reservation, ?float $prixRealise, ?string $commentaire, string $administrateur): void
    {
        if ($reservation->getStatut() !== 'CONFIRMEE') {
            throw new \Exception('Seules les réservations confirmées peuvent être marquées comme réalisées');
        }

        $reservation->setStatut('REALISEE');

        $this->addHistory(
            $reservation,
            'COMPLETION',
            'CONFIRMEE',
            'REALISEE',
            $administrateur
        );

        $this->entityManager->flush();

        // Notification
        $this->notificationService->createNotification(
            'Intervention réalisée',
            'Votre intervention a été réalisée avec succès. Merci!',
            'completion',
            $reservation
        );

        // Email
        $this->emailService->sendCompletionEmail($reservation);
    }

    /**
     * Générer un numéro de réservation unique
     */
    public function generateReservationNumber(): string
    {
        $date = new DateTime();
        $timestamp = $date->getTimestamp();
        $random = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
        return 'RES-' . $date->format('Ymd') . '-' . $random;
    }

    /**
     * Ajouter un historique de modification
     */
    private function addHistory(Reservation $reservation, string $action, string $oldValue, string $newValue, ?string $administrateur): void
    {
        $history = new ReservationHistory();
        $history->setReservation($reservation);
        $history->setAction($action);
        $history->setAncienneValeur($oldValue);
        $history->setNouvelleValeur($newValue);
        $history->setAdministrateur($administrateur);
        $history->setDateAction(new DateTime());
        $history->setHeureAction(new DateTime());

        $reservation->addHistory($history);
        $this->entityManager->persist($history);
    }

    /**
     * Récupérer les statistiques du dashboard
     */
    public function getDashboardStats(): array
    {
        return $this->reservationRepository->getStats();
    }
}
