<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/panel')]
#[IsGranted('ROLE_ADMIN')]
class AdminPanelController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_panel_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/panel/dashboard.html.twig', [
            'current_page' => 'dashboard',
        ]);
    }

    #[Route('/calendar/{view}', name: 'app_admin_panel_calendar', defaults: ['view' => 'month'])]
    public function calendar(string $view = 'month'): Response
    {
        return $this->render('admin/panel/calendar.html.twig', [
            'current_page' => 'calendrier',
            'view' => $view,
        ]);
    }

    #[Route('/reservations/{status}', name: 'app_admin_panel_reservations', defaults: ['status' => 'pending'])]
    public function reservations(string $status = 'pending'): Response
    {
        return $this->render('admin/panel/reservations.html.twig', [
            'current_page' => 'reservations',
            'status' => $status,
        ]);
    }

    #[Route('/clients', name: 'app_admin_panel_clients')]
    public function clients(): Response
    {
        return $this->render('admin/panel/clients.html.twig', [
            'current_page' => 'clients',
        ]);
    }

    #[Route('/technicians', name: 'app_admin_panel_technicians')]
    public function technicians(): Response
    {
        return $this->render('admin/panel/technicians.html.twig', [
            'current_page' => 'technicians',
        ]);
    }

    #[Route('/services', name: 'app_admin_panel_services')]
    public function services(): Response
    {
        return $this->render('admin/panel/services.html.twig', [
            'current_page' => 'services',
        ]);
    }

    #[Route('/payments', name: 'app_admin_panel_payments')]
    public function payments(): Response
    {
        return $this->render('admin/panel/payments.html.twig', [
            'current_page' => 'payments',
        ]);
    }

    #[Route('/invoices', name: 'app_admin_panel_invoices')]
    public function invoices(): Response
    {
        return $this->render('admin/panel/invoices.html.twig', [
            'current_page' => 'payments',
        ]);
    }

    #[Route('/interventions', name: 'app_admin_panel_interventions')]
    public function interventions(): Response
    {
        return $this->render('admin/panel/interventions.html.twig', [
            'current_page' => 'interventions',
        ]);
    }

    #[Route('/reports', name: 'app_admin_panel_reports')]
    public function reports(): Response
    {
        return $this->render('admin/panel/reports.html.twig', [
            'current_page' => 'interventions',
        ]);
    }

    #[Route('/reviews', name: 'app_admin_panel_reviews')]
    public function reviews(): Response
    {
        return $this->render('admin/panel/reviews.html.twig', [
            'current_page' => 'reviews',
        ]);
    }

    #[Route('/zones', name: 'app_admin_panel_zones')]
    public function zones(): Response
    {
        return $this->render('admin/panel/zones.html.twig', [
            'current_page' => 'zones',
        ]);
    }

    #[Route('/statistics', name: 'app_admin_panel_statistics')]
    public function statistics(): Response
    {
        return $this->render('admin/panel/statistics.html.twig', [
            'current_page' => 'statistics',
        ]);
    }

    #[Route('/notifications', name: 'app_admin_panel_notifications')]
    public function notifications(): Response
    {
        return $this->render('admin/panel/notifications.html.twig', [
            'current_page' => 'notifications',
        ]);
    }

    #[Route('/settings', name: 'app_admin_panel_settings')]
    public function settings(): Response
    {
        return $this->render('admin/panel/settings.html.twig', [
            'current_page' => 'settings',
        ]);
    }
}
