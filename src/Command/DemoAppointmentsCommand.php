<?php

namespace App\Command;

use App\Entity\Appointment;
use App\Entity\Client;
use App\Entity\Devis;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Adds a few example appointments (past, done, to handle, upcoming) so the admin can see how the pages look.
 * They all use an @exemple.ch address; --supprimer removes them and their quote requests.
 */
#[AsCommand(name: 'app:rdv-demo', description: 'Ajoute (ou supprime avec --supprimer) des rendez-vous d\'exemple')]
class DemoAppointmentsCommand extends Command
{
    private const DOMAIN = '@exemple.ch';

    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('supprimer', null, InputOption::VALUE_NONE, 'Supprime les rendez-vous d\'exemple');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('supprimer')) {
            $n = $this->em->createQuery('DELETE FROM App\Entity\Appointment a WHERE a.clientEmail LIKE :d')->setParameter('d', '%'.self::DOMAIN)->execute();
            $devis = $this->em->createQuery('SELECT d FROM App\Entity\Devis d JOIN d.client c WHERE c.email LIKE :d')->setParameter('d', '%'.self::DOMAIN)->getResult();
            foreach ($devis as $d) {
                $client = $d->getClient();
                $this->em->remove($d);
                $this->em->remove($client);
            }
            $this->em->flush();
            $io->success($n.' rendez-vous et '.count($devis).' demande(s) de devis d\'exemple supprimés.');

            return Command::SUCCESS;
        }

        // [prénom, nom, téléphone, adresse, NPA, ville, installation, panneaux, message, date, heure, statut, note interne]
        $rows = [
            ['Martine', 'Rochat', '079 123 45 67', 'Chemin des Vignes 12', '1095', 'Lutry', 'résidentiel', 18, 'Panneaux très sales après l\'hiver, accès par le jardin.', '-12 days', '09:00', 'done', 'Nettoyage fait, client très satisfait.'],
            ['Pierre', 'Favre', '078 234 56 78', 'Route de Berne 44', '1700', 'Fribourg', 'agricole', 96, 'Toit de grange, grande surface.', '-3 days', '14:00', 'confirmed', 'Prévoir la nacelle.'],
            ['Sofia', 'Bianchi', '076 345 67 89', 'Rue du Rhône 5', '1950', 'Sion', 'résidentiel', 24, 'Disponible plutôt le matin.', '+4 days', null, 'pending', null],
            ['Marc', 'Dubois', '079 456 78 90', 'Avenue de la Gare 21', '2000', 'Neuchâtel', 'industriel', 240, 'Entrepôt, merci de passer avant 10h.', '+9 days', '08:30', 'confirmed', null],
        ];

        foreach ($rows as [$prenom, $nom, $tel, $adresse, $npa, $ville, $type, $panneaux, $message, $when, $heure, $statut, $note]) {
            $email = strtolower($prenom.'.'.$nom).self::DOMAIN;
            $client = (new Client())->setPrenom($prenom)->setNom($nom)->setEmail($email)->setTelephone($tel)
                ->setAdresse($adresse)->setCodePostal($npa)->setVille($ville);
            $devis = (new Devis())->setClient($client)->setTypeInstallation($type)->setNombrePanneaux($panneaux)
                ->setMessage($message)->setStatut($statut === 'pending' ? Devis::STATUT_NOUVEAU : Devis::STATUT_ACCEPTE);

            $date = new \DateTime($when);
            $a = (new Appointment())->setClientName($prenom.' '.$nom)->setClientEmail($email)->setClientPhone($tel)
                ->setNotes($message)->setRequestedDate($date)->setStatus($statut)->setAdminNotes($note);
            if ($statut !== 'pending') {
                $a->setConfirmedDate(clone $date);
            }
            $a->setHeure($heure);

            $this->em->persist($client);
            $this->em->persist($devis);
            $this->em->persist($a);
        }
        $this->em->flush();

        $io->success(count($rows).' rendez-vous d\'exemple ajoutés (adresses @exemple.ch). Pour les enlever : php bin/console app:rdv-demo --supprimer --env=prod');

        return Command::SUCCESS;
    }
}
