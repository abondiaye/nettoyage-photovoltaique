<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sends visitors who arrive on an old or secondary domain (panneauvoltaique.net,
 * www., sirius-solar-services.ch…) to the main domain with a permanent redirect, keeping the path.
 * Disabled when CANONICAL_HOST is empty (local development).
 */
class CanonicalHostSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('%env(default::CANONICAL_HOST)%')] private readonly ?string $canonicalHost = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 256]];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->canonicalHost) {
            return;
        }

        $request = $event->getRequest();
        if (strcasecmp($request->getHost(), $this->canonicalHost) === 0) {
            return;
        }

        $event->setResponse(new RedirectResponse(
            'https://'.$this->canonicalHost.$request->getRequestUri(),
            301,
        ));
    }
}
