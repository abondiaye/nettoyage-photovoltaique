<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * After login: administrators go straight to the admin dashboard,
 * everyone else goes back to the page they wanted (or the home page).
 */
class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    use TargetPathTrait;

    public function __construct(private readonly UrlGeneratorInterface $urls)
    {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        if (in_array('ROLE_ADMIN', $token->getRoleNames(), true)) {
            return new RedirectResponse($this->urls->generate('app_admin_dashboard'));
        }

        $firewall = 'main'; // the site's only login firewall (config/packages/security.yaml)
        if ($request->hasSession() && ($target = $this->getTargetPath($request->getSession(), $firewall))) {
            $this->removeTargetPath($request->getSession(), $firewall);

            return new RedirectResponse($target);
        }

        return new RedirectResponse($this->urls->generate('app_home'));
    }
}
