<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/** Legal pages required for a Swiss business website (imprint + data protection, nLPD). */
class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'app_legal_notice')]
    public function notice(): Response
    {
        return $this->render('legal/notice.html.twig');
    }

    #[Route('/confidentialite', name: 'app_legal_privacy')]
    public function privacy(): Response
    {
        return $this->render('legal/privacy.html.twig');
    }
}
