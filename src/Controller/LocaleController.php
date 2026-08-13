<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LocaleController extends AbstractController
{
    #[Route('/set-locale/{locale}', name: 'set_locale')]
    public function setLocale(string $locale, Request $request): Response
    {
        $supported = ['fr', 'de', 'it'];

        if (!in_array($locale, $supported)) {
            $locale = 'fr';
        }

        $referer = $request->headers->get('referer', $this->generateUrl('app_home'));

        $response = $this->redirect($referer);
        $response->headers->setCookie(
            \Symfony\Component\HttpFoundation\Cookie::create('_locale')
                ->withValue($locale)
                ->withExpires(new \DateTime('+1 year'))
                ->withPath('/')
        );

        return $response;
    }
}
