<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use App\Repository\CommentRepository;
use App\Repository\MessageRepository;
use App\Repository\PointRepository;
use App\Repository\UserRepository;
use App\Service\SwissCanton;
use Doctrine\ORM\EntityManagerInterface;
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
        MessageRepository $messageRepo,
        EntityManagerInterface $em
    ): Response {
        // Queries go through DQL: Devis/Client point to repository classes that do not exist.
        $devis = $em->createQuery('SELECT d, c FROM App\Entity\Devis d LEFT JOIN d.client c ORDER BY d.dateCreation DESC')->getResult();
        $customers = $em->createQuery('SELECT c FROM App\Entity\Customer c')->getResult();
        $appointments = $appointmentRepo->findAll();
        $messages = $messageRepo->findBy([], ['createdAt' => 'DESC']);

        // Last 6 months, oldest first: demandes de devis, rendez-vous, messages.
        $months = [];
        $labels = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
        for ($i = 5; $i >= 0; --$i) {
            $m = new \DateTimeImmutable("first day of -$i month");
            $months[$m->format('Y-m')] = ['label' => $labels[(int) $m->format('n') - 1], 'devis' => 0, 'rdv' => 0, 'messages' => 0];
        }
        $bump = static function (?\DateTimeInterface $d, string $key) use (&$months): void {
            if ($d && isset($months[$d->format('Y-m')])) {
                ++$months[$d->format('Y-m')][$key];
            }
        };
        foreach ($devis as $d) { $bump($d->getDateCreation(), 'devis'); }
        foreach ($appointments as $a) { $bump($a->getCreatedAt(), 'rdv'); }
        foreach ($messages as $m) { $bump($m->getCreatedAt(), 'messages'); }

        // Clients per canton, from the postal codes of quote requests and bookings.
        $cantons = [];
        foreach ($devis as $d) {
            if ($code = SwissCanton::fromPostalCode($d->getClient()?->getCodePostal())) {
                $cantons[$code] = ($cantons[$code] ?? 0) + 1;
            }
        }
        foreach ($customers as $c) {
            if ($code = SwissCanton::fromPostalCode($c->getCodePostal())) {
                $cantons[$code] = ($cantons[$code] ?? 0) + 1;
            }
        }
        arsort($cantons);

        $countDevis = static fn (string $statut): int => count(array_filter($devis, static fn ($d) => $d->getStatut() === $statut));
        $countRdv = static fn (string $status): int => count(array_filter($appointments, static fn ($a) => $a->getStatus() === $status));

        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                'devis' => count($devis),
                'devis_nouveaux' => $countDevis('nouveau'),
                'rdv' => count($appointments),
                'rdv_attente' => $countRdv('pending'),
                'rdv_confirmes' => $countRdv('confirmed'),
                'messages' => count($messages),
                'clients' => count($customers) + count(array_unique(array_filter(array_map(static fn ($d) => $d->getClient()?->getEmail(), $devis)))),
            ],
            'months' => array_values($months),
            'cantons' => $cantons,
            'canton_names' => SwissCanton::NAMES,
            'recent_devis' => array_slice($devis, 0, 6),
            'recent_messages' => array_slice($messages, 0, 5),
            // Kept for older parts of the template.
            'total_appointments' => count($appointments),
            'pending_appointments' => $countRdv('pending'),
            'confirmed_appointments' => $countRdv('confirmed'),
            'proposed_appointments' => $countRdv('proposed'),
            'refused_appointments' => $countRdv('refused'),
            'total_messages' => count($messages),
        ]);
    }


    #[Route('/members', name: 'app_admin_members')]
    public function members(UserRepository $userRepo): Response
    {
        $members = $userRepo->findBy([], ['createdAt' => 'DESC']);

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
