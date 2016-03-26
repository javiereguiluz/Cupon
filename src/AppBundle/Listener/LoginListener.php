<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Listener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Listener del evento SecurityInteractive que se utiliza para redireccionar
 * al usuario recién logueado a la portada de su ciudad.
 */
class LoginListener
{
    /** @var AuthorizationChecker */
    private $checker;
    /** @var Router */
    private $router;
    private $ciudad;

    public function __construct(AuthorizationChecker $checker, Router $router)
    {
        $this->checker = $checker;
        $this->router = $router;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $this->ciudad = $token->getUser()->getCiudad()->getSlug();
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (null !== $this->ciudad) {
            if ($this->checker->isGranted('ROLE_TIENDA')) {
                $portada = $this->router->generate('extranet_portada');
            } else {
                $portada = $this->router->generate('portada', array(
                    'ciudad' => $this->ciudad,
                ));
            }

            $event->setResponse(new RedirectResponse($portada));
            $event->stopPropagation();
        }
    }
}
