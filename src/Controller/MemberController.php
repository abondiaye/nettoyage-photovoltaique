<?php

namespace App\Controller;

use App\Service\PointService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/member')]
#[IsGranted('ROLE_MEMBER')]
class MemberController extends AbstractController
{
    #[Route('/dashboard', name: 'app_member_dashboard')]
    public function dashboard(PointService $pointService): Response
    {
        $user = $this->getUser();
        $pointsHistory = $pointService->getPointsHistory($user);
        $discounts = $pointService->getAvailableDiscounts($user);

        return $this->render('member/dashboard.html.twig', [
            'user' => $user,
            'points' => $user->getPoints(),
            'pointsHistory' => $pointsHistory,
            'discounts' => $discounts,
        ]);
    }

    #[Route('/appointments', name: 'app_member_appointments')]
    public function appointments(): Response
    {
        $user = $this->getUser();

        return $this->render('member/appointments.html.twig', [
            'appointments' => $user->getAppointments(),
        ]);
    }
}
