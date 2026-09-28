<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Appointments management for the admin: month calendar, list by status,
 * and every action on an appointment (confirm, propose another date, move, refuse, cancel, done, delete, create).
 *
 * The appointments come from the quote form (/devis), which creates one "pending" appointment per request.
 */
#[IsGranted('ROLE_ADMIN')]
class AdminAppointmentController extends AbstractController
{
    public const STATUSES = [
        'pending' => 'À traiter',
        'proposed' => 'Autre date proposée',
        'confirmed' => 'Confirmé',
        'done' => 'Terminé',
        'refused' => 'Refusé',
        'cancelled' => 'Annulé',
    ];

    #[Route('/admin/appointments', name: 'app_admin_appointments')]
    public function index(Request $request, AppointmentRepository $repo, EntityManagerInterface $em): Response
    {
        $today = new \DateTimeImmutable('today');
        $month = \DateTimeImmutable::createFromFormat('!Y-m', (string) $request->query->get('mois')) ?: $today->modify('first day of this month');

        $all = $repo->findBy([], ['requestedDate' => 'ASC']);
        $when = static fn (Appointment $a) => $a->getConfirmedDate() ?? $a->getRequestedDate();

        // Month grid, weeks starting on Monday.
        $start = $month->modify('monday this week');
        if ($start > $month) {
            $start = $start->modify('-7 days');
        }
        $end = $month->modify('last day of this month')->modify('sunday this week');
        $byDay = [];
        foreach ($all as $a) {
            if (in_array($a->getStatus(), ['refused', 'cancelled'], true)) {
                continue;
            }
            $byDay[$when($a)->format('Y-m-d')][] = $a;
        }
        foreach ($byDay as &$list) {
            usort($list, static fn ($x, $y) => strcmp((string) $x->getHeure(), (string) $y->getHeure()));
        }
        unset($list);
        $weeks = [];
        for ($d = $start; $d <= $end; $d = $d->modify('+1 day')) {
            $weeks[(int) floor($start->diff($d)->days / 7)][] = [
                'date' => $d,
                'in_month' => $d->format('m') === $month->format('m'),
                'today' => $d == $today,
                'items' => $byDay[$d->format('Y-m-d')] ?? [],
            ];
        }

        // Lists for the actions.
        $groups = ['todo' => [], 'upcoming' => [], 'past' => [], 'closed' => []];
        foreach ($all as $a) {
            $groups[match (true) {
                in_array($a->getStatus(), ['pending', 'proposed'], true) => 'todo',
                $a->getStatus() === 'confirmed' && $when($a) >= $today => 'upcoming',
                in_array($a->getStatus(), ['confirmed', 'done'], true) => 'past',
                default => 'closed',
            }][] = $a;
        }
        $groups['past'] = array_reverse($groups['past']);
        $groups['closed'] = array_reverse($groups['closed']);

        // Address and installation from the quote request with the same e-mail (latest first).
        $quotes = [];
        foreach ($em->createQuery('SELECT d, c FROM App\Entity\Devis d JOIN d.client c ORDER BY d.dateCreation ASC')->getResult() as $d) {
            $quotes[strtolower((string) $d->getClient()->getEmail())] = $d;
        }

        return $this->render('admin/appointments.html.twig', [
            'month' => $month,
            'prev' => $month->modify('-1 month'),
            'next' => $month->modify('+1 month'),
            'weeks' => $weeks,
            'groups' => $groups,
            'quotes' => $quotes,
            'statuses' => self::STATUSES,
            'today' => $today,
        ]);
    }

    #[Route('/admin/calendar', name: 'app_admin_calendar')]
    public function calendar(): Response
    {
        return $this->redirectToRoute('app_admin_appointments');
    }

    #[Route('/admin/appointments/new', name: 'app_admin_appointment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $values = [
            'nom' => '', 'email' => '', 'telephone' => '', 'notes' => '',
            'date' => $request->query->get('date', (new \DateTimeImmutable('tomorrow'))->format('Y-m-d')),
            'heure' => '09:00',
        ];
        $errors = [];

        if ($request->isMethod('POST')) {
            foreach (array_keys($values) as $k) {
                $values[$k] = trim((string) $request->request->get($k, ''));
            }
            if (!$this->isCsrfTokenValid('rdv-new', (string) $request->request->get('_token'))) {
                $errors[] = 'La session a expiré, merci de renvoyer le formulaire.';
            }
            if ($values['nom'] === '') {
                $errors[] = 'Le nom du client est obligatoire.';
            }
            if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'L\'adresse e-mail n\'est pas valide.';
            }
            $date = \DateTime::createFromFormat('!Y-m-d', $values['date']) ?: null;
            if (!$date) {
                $errors[] = 'La date n\'est pas valide.';
            }

            if (!$errors) {
                $a = (new Appointment())
                    ->setClientName($values['nom'])
                    ->setClientEmail($values['email'])
                    ->setClientPhone($values['telephone'] ?: null)
                    ->setNotes($values['notes'] ?: null)
                    ->setRequestedDate($date)
                    ->setConfirmedDate(clone $date)
                    ->setStatus('confirmed');
                $a->setHeure($this->time($values['heure']));
                $em->persist($a);
                $em->flush();
                $this->addFlash('success', 'Rendez-vous ajouté le '.$date->format('d/m/Y').($a->getHeure() ? ' à '.$a->getHeure() : '').' avec '.$a->getClientName().'.');

                return $this->redirectToRoute('app_admin_appointments', ['mois' => $date->format('Y-m')]);
            }
        }

        return $this->render('admin/appointment_new.html.twig', ['values' => $values, 'errors' => $errors]);
    }

    /** Confirm (or move) an appointment to a date and time. */
    #[Route('/admin/appointment/{id}/accept', name: 'app_appointment_accept', methods: ['POST'])]
    public function accept(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        return $this->act($appointment, $request, $em, 'confirmed', 'Rendez-vous confirmé', true);
    }

    #[Route('/admin/appointment/{id}/propose', name: 'app_appointment_propose', methods: ['POST'])]
    public function propose(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        return $this->act($appointment, $request, $em, 'proposed', 'Autre date proposée', true);
    }

    #[Route('/admin/appointment/{id}/refuse', name: 'app_appointment_refuse', methods: ['POST'])]
    public function refuse(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        return $this->act($appointment, $request, $em, 'refused', 'Demande refusée');
    }

    #[Route('/admin/appointment/{id}/cancel', name: 'app_appointment_cancel', methods: ['POST'])]
    public function cancel(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        return $this->act($appointment, $request, $em, 'cancelled', 'Rendez-vous annulé');
    }

    #[Route('/admin/appointment/{id}/done', name: 'app_appointment_done', methods: ['POST'])]
    public function done(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        return $this->act($appointment, $request, $em, 'done', 'Intervention marquée comme terminée');
    }

    #[Route('/admin/appointment/{id}/reopen', name: 'app_appointment_reopen', methods: ['POST'])]
    public function reopen(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        return $this->act($appointment, $request, $em, 'pending', 'Demande remise « à traiter »');
    }

    #[Route('/admin/appointment/{id}/delete', name: 'app_appointment_delete', methods: ['POST'])]
    public function delete(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('rdv-'.$appointment->getId(), (string) $request->request->get('_token'))) {
            $name = $appointment->getClientName();
            $em->remove($appointment);
            $em->flush();
            $this->addFlash('success', 'Rendez-vous de '.$name.' supprimé.');
        }

        return $this->back($request);
    }

    private function act(Appointment $a, Request $request, EntityManagerInterface $em, string $status, string $message, bool $withDate = false): Response
    {
        if (!$this->isCsrfTokenValid('rdv-'.$a->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('success', 'La page avait expiré : rien n\'a été modifié, merci de recommencer.');

            return $this->back($request);
        }
        $a->setStatus($status);
        if ($withDate) {
            $date = \DateTime::createFromFormat('!Y-m-d', (string) $request->request->get('date')) ?: null;
            $a->setConfirmedDate($date ?? $a->getConfirmedDate() ?? $a->getRequestedDate());
            $a->setHeure($this->time((string) $request->request->get('heure')) ?? $a->getHeure());
        }
        if ($status === 'pending') {
            $a->setConfirmedDate(null);
        }
        $notes = trim((string) $request->request->get('adminNotes'));
        if ($notes !== '') {
            $a->setAdminNotes($notes);
        }
        $a->setUpdatedAt(new \DateTime());
        $em->flush();

        $when = $a->getConfirmedDate() ?? $a->getRequestedDate();
        $this->addFlash('success', $message.' : '.$a->getClientName().($withDate ? ', le '.$when->format('d/m/Y').($a->getHeure() ? ' à '.$a->getHeure() : '') : '').'.');

        return $this->back($request);
    }

    /** Back to the calendar month the admin was looking at. */
    private function back(Request $request): Response
    {
        $month = (string) $request->request->get('mois');

        return $this->redirectToRoute('app_admin_appointments', preg_match('/^\d{4}-\d{2}$/', $month) ? ['mois' => $month] : []);
    }

    private function time(string $value): ?string
    {
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) ? $value : null;
    }
}
