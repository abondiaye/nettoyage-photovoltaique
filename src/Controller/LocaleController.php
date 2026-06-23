<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LocaleController
{
    private const SUPPORTED_LOCALES = ['fr', 'en'];

    #[Route('/locale/{locale}', name: 'app_locale_switch')]
    public function switch(string $locale, Request $request): RedirectResponse
    {
        if (\in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $request->getSession()->set('_locale', $locale);
        }

        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer ?: '/');
    }
}
