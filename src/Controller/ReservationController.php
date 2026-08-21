<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Customer;
use App\Service\ReservationService;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class ReservationController extends AbstractController
{
    #[Route('/reserver', name: 'reservation_form')]
    public function form(): Response
    {
        return $this->render('reservation/form.html.twig');
    }

    #[Route('/reserver', name: 'reservation_submit', methods: ['POST'])]
    public function submit(
        Request $request,
        EntityManagerInterface $entityManager,
        ReservationService $reservationService,
        NotificationService $notificationService
    ): Response {
        // Récupérer les données du formulaire
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        $email = $request->request->get('email');
        $telephone = $request->request->get('telephone');
        $adresse = $request->request->get('adresse');
        $ville = $request->request->get('ville');
        $codePostal = $request->request->get('codePostal');
        $dateSouhaitee = $request->request->get('dateSouhaitee');
        $heureSouhaitee = $request->request->get('heureSouhaitee');
        $nombrePanneaux = $request->request->get('nombrePanneaux');
        $typeToiture = $request->request->get('typeToiture');
        $notes = $request->request->get('notes');

        // Validation basique
        if (!$nom || !$email || !$telephone || !$dateSouhaitee || !$heureSouhaitee) {
            return $this->json([
                'success' => false,
                'message' => 'Tous les champs obligatoires doivent être remplis'
            ], 400);
        }

        try {
            // Créer ou récupérer le client
            $customer = new Customer();
            $customer->setNom($nom);
            $customer->setPrenom($prenom);
            $customer->setEmail($email);
            $customer->setTelephone($telephone);
            $customer->setAdresse($adresse);
            $customer->setVille($ville);
            $customer->setCodePostal($codePostal);

            $entityManager->persist($customer);

            // Créer la réservation
            $reservation = new Reservation();
            $reservation->setNumero($reservationService->generateReservationNumber());
            $reservation->setCustomer($customer);
            $reservation->setDateSouhaitee(DateTime::createFromFormat('Y-m-d', $dateSouhaitee));
            $reservation->setHeureSouhaitee(DateTime::createFromFormat('H:i', $heureSouhaitee));
            $reservation->setNombrePanneaux((int)$nombrePanneaux);
            $reservation->setTypeToiture($typeToiture);
            $reservation->setNotes($notes);
            $reservation->setStatut('EN_ATTENTE');
            $reservation->setDateCreation(new DateTime());

            // Calculer prix estimé
            $prixEstime = $this->calculatePrice((int)$nombrePanneaux, $typeToiture);
            $reservation->setPrixEstime($prixEstime);

            $entityManager->persist($reservation);
            $entityManager->flush();

            // Créer une notification d'alerte pour l'admin
            $notificationService->createNotification(
                'Nouvelle demande de réservation',
                'Une nouvelle demande de nettoyage de ' . $nombrePanneaux . ' panneaux a été reçue de ' . $nom . ' ' . $prenom,
                'new_request',
                $reservation
            );

            return $this->json([
                'success' => true,
                'message' => 'Votre demande a été enregistrée avec succès',
                'reservationNumber' => $reservation->getNumero()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'enregistrement de votre demande: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/reservation/{numero}', name: 'reservation_status')]
    public function status(
        string $numero,
        \App\Repository\ReservationRepository $reservationRepository
    ): Response {
        $reservation = $reservationRepository->findOneBy(['numero' => $numero]);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        return $this->render('reservation/status.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * Calculer le prix estimé basé sur nombre de panneaux et type de toiture
     */
    private function calculatePrice(int $nombrePanneaux, string $typeToiture): float
    {
        $prixBase = 50; // CHF par panneau
        $multiplicateurs = [
            'plat' => 1.0,
            'incline_leger' => 1.1,
            'incline_moyen' => 1.2,
            'incline_fort' => 1.3,
            'avec_obstacles' => 1.5,
        ];

        $multiplicateur = $multiplicateurs[$typeToiture] ?? 1.0;
        return $nombrePanneaux * $prixBase * $multiplicateur;
    }
}
