<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LocaleSwitchController extends AbstractController
{
    #[Route('/locale/{locale}', name: 'app_locale_switch')]
    public function switchLocale(string $locale, Request $request): Response
    {
        // Locales autorisées
        $allowedLocales = ['fr', 'en', 'de', 'it', 'rm'];

        if (!in_array($locale, $allowedLocales)) {
            $locale = 'fr'; // Par défaut français
        }

        // Sauvegarder dans la session
        $request->getSession()->set('_locale', $locale);

        // Rediriger vers la page précédente ou l'accueil
        $referer = $request->headers->get('referer');

        if ($referer && strpos($referer, $request->getSchemeAndHttpHost()) === 0) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('home');
    }
}
