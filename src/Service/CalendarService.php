<?php

namespace App\Service;

use App\Entity\Appointment;
use App\Entity\TimeSlot;
use App\Repository\AppointmentRepository;
use App\Repository\TimeSlotRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class CalendarService
{
    private const BUSINESS_HOURS = [
        1 => [['start' => '08:00', 'end' => '12:00'], ['start' => '13:30', 'end' => '18:30']], // Mon
        2 => [['start' => '08:00', 'end' => '12:00'], ['start' => '13:30', 'end' => '18:30']], // Tue
        3 => [['start' => '08:00', 'end' => '12:00'], ['start' => '13:30', 'end' => '18:30']], // Wed
        4 => [['start' => '08:00', 'end' => '12:00'], ['start' => '13:30', 'end' => '18:30']], // Thu
        5 => [['start' => '08:00', 'end' => '12:00'], ['start' => '13:30', 'end' => '18:30']], // Fri
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private TimeSlotRepository $timeSlotRepo,
        private AppointmentRepository $appointmentRepo,
    ) {
    }

    public function initializeTimeSlots(): void
    {
        foreach (self::BUSINESS_HOURS as $day => $periods) {
            foreach ($periods as $period) {
                $this->createHourlySlots($day, $period['start'], $period['end']);
            }
        }
    }

    private function createHourlySlots(int $dayOfWeek, string $startTime, string $endTime): void
    {
        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);

        while ($start < $end) {
            $next = $start->add(new \DateInterval('PT1H'));

            $timeSlot = new TimeSlot();
            $timeSlot->setDayOfWeek($dayOfWeek);
            $timeSlot->setStartTime($start->format('H:i'));
            $timeSlot->setEndTime($next->format('H:i'));
            $timeSlot->setMaxAppointments(4);

            $this->em->persist($timeSlot);
        }
        $this->em->flush();
    }

    public function getAvailableSlots(\DateTimeImmutable $date): array
    {
        $dayOfWeek = (int) $date->format('N');

        if ($dayOfWeek > 5) {
            return [];
        }

        $slots = $this->timeSlotRepo->findBy(['dayOfWeek' => $dayOfWeek]);
        $available = [];

        foreach ($slots as $slot) {
            $count = $this->appointmentRepo->count([
                'date' => $date,
                'timeSlot' => $slot->getStartTime(),
                'status' => Appointment::STATUS_SCHEDULED,
            ]);

            if ($count < $slot->getMaxAppointments()) {
                $available[] = [
                    'time' => $slot->getStartTime(),
                    'available' => $slot->getMaxAppointments() - $count,
                    'maxSlots' => $slot->getMaxAppointments(),
                ];
            }
        }

        return $available;
    }

    public function isSlotAvailable(\DateTimeImmutable $date, string $timeSlot): bool
    {
        $dayOfWeek = (int) $date->format('N');

        $slot = $this->timeSlotRepo->findOneBy([
            'dayOfWeek' => $dayOfWeek,
            'startTime' => $timeSlot,
        ]);

        if (!$slot) {
            return false;
        }

        $count = $this->appointmentRepo->count([
            'date' => $date,
            'timeSlot' => $timeSlot,
            'status' => Appointment::STATUS_SCHEDULED,
        ]);

        return $count < $slot->getMaxAppointments();
    }

    public function getNextBusinessDays(int $count = 30): array
    {
        $days = [];
        $date = new DateTimeImmutable('tomorrow');

        while (count($days) < $count) {
            $dayOfWeek = (int) $date->format('N');
            if ($dayOfWeek <= 5) {
                $days[] = $date;
            }
            $date = $date->add(new \DateInterval('P1D'));
        }

        return $days;
    }

    public function getAppointmentsByDate(\DateTimeImmutable $date): array
    {
        return $this->appointmentRepo->findBy(
            ['date' => $date],
            ['timeSlot' => 'ASC']
        );
    }
}
