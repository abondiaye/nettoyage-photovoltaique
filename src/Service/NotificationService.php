<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Créer une notification
     */
    public function createNotification(
        string $titre,
        string $message,
        string $type,
        ?Reservation $reservation = null
    ): Notification {
        $notification = new Notification();
        $notification->setTitre($titre);
        $notification->setMessage($message);
        $notification->setType($type);
        $notification->setLue(false);
        $notification->setDateCreation(new DateTime());

        if ($reservation) {
            $notification->setReservation($reservation);
        }

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->setLue(true);
        $this->entityManager->flush();
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(): void
    {
        $this->entityManager->createQuery(
            'UPDATE App\Entity\Notification n SET n.lue = true WHERE n.lue = false'
        )->execute();
    }

    /**
     * Supprimer une notification
     */
    public function deleteNotification(Notification $notification): void
    {
        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }

    /**
     * Créer des notifications en masse pour les interventions à venir
     */
    public function createUpcomingInterventionNotifications(): void
    {
        $tomorrow = new DateTime('tomorrow');
        $afterTomorrow = new DateTime('tomorrow');
        $afterTomorrow->modify('+1 day');

        // Notifications pour interventions demain
        $query = $this->entityManager->createQuery(
            'SELECT r FROM App\Entity\Reservation r WHERE r.statut = :statut AND r.dateSouhaitee BETWEEN :start AND :end'
        );
        $query->setParameter('statut', 'CONFIRMEE');
        $query->setParameter('start', $tomorrow);
        $query->setParameter('end', $afterTomorrow);

        $reservations = $query->getResult();

        foreach ($reservations as $reservation) {
            $this->createNotification(
                'Intervention demain',
                'Rappel: Une intervention est prévue demain à ' . $reservation->getHeureSouhaitee()->format('H:i'),
                'reminder',
                $reservation
            );
        }
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function getUnreadCount(): int
    {
        $query = $this->entityManager->createQuery(
            'SELECT COUNT(n.id) FROM App\Entity\Notification n WHERE n.lue = false'
        );

        return (int) $query->getSingleScalarResult();
    }
}
