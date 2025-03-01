<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\InstallBundle\Listener;

use SolidInvoice\InstallBundle\Exception\ApplicationInstalledException;
use SolidInvoice\UserBundle\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use function in_array;

/**
 * Listener class to intercept requests
 * and redirect to the installer if necessary.
 */
final class RequestListener implements EventSubscriberInterface
{
    public const INSTALLER_ROUTE = '_install_check_requirements';

    /**
     * Core routes.
     *
     * @var list<string>
     */
    private array $allowRoutes = [];

    /**
     * @var list<string>
     */
    private const INSTALL_ROUTES = [
        self::INSTALLER_ROUTE,
        '_install_config',
        '_install_install',
        ...self::SETUP_ROUTES
    ];

    /**
     * @var list<string>
     */
    private const SETUP_ROUTES = [
        '_install_setup',
        '_install_finish',
    ];

    /**
     * @var list<string>
     */
    private const DEBUG_ROUTES = [
        '_wdt',
        '_profiler',
        '_profiler_search',
        '_profiler_search_bar',
        '_profiler_search_results',
        '_profiler_router',
    ];

    private ?string $installed;

    private RouterInterface $router;

    private UserRepositoryInterface $userRepository;

    private bool $debug;

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function __construct(
        RouterInterface $router,
        UserRepositoryInterface $userRepository,
        ?string $installed,
        bool $debug = false
    ) {
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->installed = $installed;
        $this->allowRoutes = self::INSTALL_ROUTES;
        $this->debug = $debug;

        if ($this->debug) {
            $this->allowRoutes = array_merge($this->allowRoutes, self::DEBUG_ROUTES);
        }
    }

    /**
     * @throws ApplicationInstalledException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        $route = $request->get('_route');

        if (null !== $this->installed) {
            // If the application is installed, but we don't have any users
            // Redirect to the setup page
            if ($this->userRepository->getUserCount() === 0) {
                if (! in_array($route, [...self::SETUP_ROUTES, ...($this->debug ? self::DEBUG_ROUTES : [])], true)) {
                    $this->redirectToRoute($event, '_install_setup');
                }

                return;
            }

            // If the application is installed, and we already have users, and the installer route is requested
            // then throw an exception
            if (in_array($route, self::INSTALL_ROUTES, true) && ! $session->has('installation_step')) {
                throw new ApplicationInstalledException();
            }
        } elseif (! in_array($route, $this->allowRoutes, true)) {
            $this->redirectToRoute($event, self::INSTALLER_ROUTE);
        }
    }

    private function redirectToRoute(RequestEvent $event, string $route): void
    {
        $response = new RedirectResponse($this->router->generate($route));

        $event->setResponse($response);
        $event->stopPropagation();
    }
}
