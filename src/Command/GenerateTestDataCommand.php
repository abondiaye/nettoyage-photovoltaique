<?php

namespace App\Command;

use App\Entity\Reservation;
use App\Entity\Customer;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use DateTime;
use DateInterval;

#[AsCommand(
    name: 'app:generate-test-data',
    description: 'Génère des données de test pour la base de données',
)]
class GenerateTestDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Clients de test
        $clients = [
            ['Jean', 'Dupont', 'jean.dupont@example.com', '077 123 45 67', 'Rue de la Paix 10', '1200', 'Genève'],
            ['Marie', 'Martin', 'marie.martin@example.com', '077 234 56 78', 'Avenue des Alpes 5', '1201', 'Genève'],
            ['Pierre', 'Bernard', 'pierre.bernard@example.com', '077 345 67 89', 'Boulevard de la Liberté 20', '1202', 'Genève'],
            ['Sophie', 'Lefevre', 'sophie.lefevre@example.com', '077 456 78 90', 'Place du Marché 15', '1204', 'Vernier'],
            ['Thomas', 'Garcia', 'thomas.garcia@example.com', '077 567 89 01', 'Chemin des Roses 8', '1211', 'Conches'],
        ];

        $statuses = ['EN_ATTENTE', 'CONFIRMEE', 'REALISEE', 'REFUSEE'];
        $toitureTypes = ['plat', 'incline_leger', 'incline_moyen', 'incline_fort', 'avec_obstacles'];

        $reservations = [];
        $io->writeln('<info>Génération des clients et réservations...</info>');

        foreach ($clients as $index => $clientData) {
            $customer = new Customer();
            $customer->setNom($clientData[0]);
            $customer->setPrenom($clientData[1]);
            $customer->setEmail($clientData[2]);
            $customer->setTelephone($clientData[3]);
            $customer->setAdresse($clientData[4]);
            $customer->setCodePostal($clientData[5]);
            $customer->setVille($clientData[6]);

            $this->entityManager->persist($customer);

            // Créer 1-3 réservations par client
            $nbReservations = rand(1, 3);
            for ($i = 0; $i < $nbReservations; $i++) {
                $reservation = new Reservation();

                // Générer numéro unique
                $reservation->setNumero('RES-' . date('Ymd') . '-' . str_pad($index * 10 + $i, 3, '0', STR_PAD_LEFT));

                $reservation->setCustomer($customer);

                // Date souhaitée: entre aujourd'hui et 30 jours
                $date = new DateTime();
                $date->add(new DateInterval('P' . rand(1, 30) . 'D'));
                $reservation->setDateSouhaitee($date);

                $time = new DateTime();
                $time->setTime(rand(8, 17), rand(0, 5) * 15);
                $reservation->setHeureSouhaitee($time);

                $nombrePanneaux = rand(4, 50);
                $reservation->setNombrePanneaux($nombrePanneaux);

                $typeToiture = $toitureTypes[array_rand($toitureTypes)];
                $reservation->setTypeToiture($typeToiture);

                // Statut aléatoire
                $statut = $statuses[array_rand($statuses)];
                $reservation->setStatut($statut);

                // Calcul du prix
                $prixBase = 50;
                $multiplicateurs = [
                    'plat' => 1.0,
                    'incline_leger' => 1.1,
                    'incline_moyen' => 1.2,
                    'incline_fort' => 1.3,
                    'avec_obstacles' => 1.5,
                ];
                $multiplicateur = $multiplicateurs[$typeToiture] ?? 1.0;
                $prixEstime = $nombrePanneaux * $prixBase * $multiplicateur;
                $reservation->setPrixEstime($prixEstime);

                $reservation->setNotes('Test data - ' . ($i + 1));
                $reservation->setDateCreation(new DateTime('-' . rand(1, 15) . ' days'));

                $this->entityManager->persist($reservation);
                $reservations[] = $reservation;
            }
        }

        // Créer des notifications
        $io->writeln('<info>Génération des notifications...</info>');
        $notificationMessages = [
            ['Nouvelle demande', 'Une nouvelle demande de nettoyage a été reçue', 'new_request'],
            ['Intervention confirmée', 'Une intervention a été confirmée', 'confirmation'],
            ['Intervention reportée', 'Une intervention a été reportée', 'reschedule'],
            ['Rappel intervention', 'Rappel: une intervention est prévue demain', 'reminder'],
        ];

        foreach (array_slice($reservations, 0, 10) as $reservation) {
            $msgData = $notificationMessages[array_rand($notificationMessages)];
            $notification = new Notification();
            $notification->setTitre($msgData[0]);
            $notification->setMessage($msgData[1]);
            $notification->setType($msgData[2]);
            $notification->setReservation($reservation);
            $notification->setLue(rand(0, 1) === 0);
            $notification->setDateCreation(new DateTime('-' . rand(1, 7) . ' days'));

            $this->entityManager->persist($notification);
        }

        // Flush tout
        $this->entityManager->flush();

        $io->success("Données de test générées avec succès!");
        $io->table(
            ['Ressource', 'Quantité'],
            [
                ['Clients', count($clients)],
                ['Réservations', count($reservations)],
                ['Notifications', 10],
            ]
        );

        $io->writeln('');
        $io->writeln('<comment>Connectez-vous avec l\'utilisateur admin et accédez à /admin/ pour voir les données.</comment>');

        return Command::SUCCESS;
    }
}
