<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AppointmentController extends AbstractController
{
    #[Route('/appointments', name: 'app_appointments')]
    public function clientCalendar(AppointmentRepository $appointmentRepo): Response
    {
        $appointments = $appointmentRepo->findAll();
        $appointmentsByDate = [];

        foreach ($appointments as $apt) {
            $date = $apt->getConfirmedDate() ?? $apt->getRequestedDate();
            $dateStr = $date->format('Y-m-d');
            if (!isset($appointmentsByDate[$dateStr])) {
                $appointmentsByDate[$dateStr] = [];
            }
            $appointmentsByDate[$dateStr][] = $apt;
        }

        return $this->render('appointment/client_calendar.html.twig', [
            'appointments' => $appointments,
            'appointmentsByDate' => $appointmentsByDate,
        ]);
    }

    #[Route('/admin/calendar', name: 'app_admin_calendar')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminCalendar(AppointmentRepository $appointmentRepo): Response
    {
        $appointments = $appointmentRepo->findBy([], ['requestedDate' => 'ASC']);

        $appointmentsByStatus = [
            'pending' => [],
            'confirmed' => [],
            'refused' => [],
            'proposed' => [],
        ];

        foreach ($appointments as $apt) {
            $appointmentsByStatus[$apt->getStatus()][] = $apt;
        }

        return $this->render('appointment/admin_calendar.html.twig', [
            'appointments' => $appointments,
            'appointmentsByStatus' => $appointmentsByStatus,
        ]);
    }

    #[Route('/admin/appointment/{id}/accept', name: 'app_appointment_accept', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function acceptAppointment(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        $appointment->setStatus('confirmed');

        $dateStr = $request->request->get('confirmedDate');
        if ($dateStr) {
            $appointment->setConfirmedDate(new \DateTime($dateStr));
        }

        $adminNotes = $request->request->get('adminNotes');
        if ($adminNotes) {
            $appointment->setAdminNotes($adminNotes);
        }

        $em->flush();

        return $this->redirectToRoute('app_admin_calendar');
    }

    #[Route('/admin/appointment/{id}/refuse', name: 'app_appointment_refuse', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function refuseAppointment(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        $appointment->setStatus('refused');

        $adminNotes = $request->request->get('adminNotes');
        if ($adminNotes) {
            $appointment->setAdminNotes($adminNotes);
        }

        $em->flush();

        return $this->redirectToRoute('app_admin_calendar');
    }

    #[Route('/admin/appointment/{id}/propose', name: 'app_appointment_propose', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function proposeAppointment(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        $appointment->setStatus('proposed');

        $proposedDate = $request->request->get('proposedDate');
        if ($proposedDate) {
            $appointment->setConfirmedDate(new \DateTime($proposedDate));
        }

        $adminNotes = $request->request->get('adminNotes');
        if ($adminNotes) {
            $appointment->setAdminNotes($adminNotes);
        }

        $em->flush();

        return $this->redirectToRoute('app_admin_calendar');
    }
}
