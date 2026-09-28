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
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $appointments = $appointmentRepo->findBy(['clientEmail' => $user->getEmail()], ['requestedDate' => 'DESC']);
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
}
