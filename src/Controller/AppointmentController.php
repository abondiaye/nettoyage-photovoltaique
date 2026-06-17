<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Service\CalendarService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/appointment')]
#[IsGranted('ROLE_MEMBER')]
class AppointmentController extends AbstractController
{
    public function __construct(
        private CalendarService $calendarService,
        private EntityManagerInterface $em,
        private AppointmentRepository $appointmentRepo,
    ) {
    }

    #[Route('/book', name: 'app_appointment_book', methods: ['GET', 'POST'])]
    public function book(Request $request): Response
    {
        $user = $this->getUser();
        $nextDays = $this->calendarService->getNextBusinessDays(30);

        if ($request->isMethod('POST')) {
            $date = new DateTimeImmutable($request->request->get('date'));
            $timeSlot = $request->request->get('timeSlot');

            if ($this->calendarService->isSlotAvailable($date, $timeSlot)) {
                $appointment = new Appointment();
                $appointment->setUser($user);
                $appointment->setDate($date);
                $appointment->setTimeSlot($timeSlot);
                $appointment->setStatus(Appointment::STATUS_SCHEDULED);

                $this->em->persist($appointment);
                $this->em->flush();

                $this->addFlash('success', 'Appointment booked successfully!');
                return $this->redirectToRoute('app_member_appointments');
            }

            $this->addFlash('error', 'This time slot is no longer available.');
        }

        $availableSlotsByDate = [];
        foreach ($nextDays as $day) {
            $availableSlotsByDate[$day->format('Y-m-d')] = $this->calendarService->getAvailableSlots($day);
        }

        return $this->render('appointment/book.html.twig', [
            'availableSlots' => $availableSlotsByDate,
            'nextDays' => $nextDays,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_appointment_cancel', methods: ['POST'])]
    public function cancel(Appointment $appointment): Response
    {
        if ($appointment->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $appointment->setStatus(Appointment::STATUS_CANCELLED);
        $this->em->persist($appointment);
        $this->em->flush();

        $this->addFlash('success', 'Appointment cancelled.');
        return $this->redirectToRoute('app_member_appointments');
    }
}
