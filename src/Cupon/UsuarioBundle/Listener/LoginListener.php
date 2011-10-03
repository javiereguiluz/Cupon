<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Cupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\UsuarioBundle\Listener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Listener del evento SecurityInteractive que se utiliza para redireccionar
 * al usuario recién logueado a la portada de su ciudad
 */
class LoginListener
{
    private $usuario, $esTienda, $router, $ciudad = null;

    public function __construct(SecurityContext $context, Router $router)
    {
        $this->contexto = $context;
        $this->router = $router;
    }
    
    public function onSecurityInteractiveLogin(Event $event)
    {
        if (null != $this->contexto) {
            $this->ciudad = $this->contexto->getToken()->getUser()->getCiudad()->getSlug();
        }
    }
    
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (null != $this->ciudad) {
            if($this->contexto->isGranted('ROLE_TIENDA')) {
                $event->setResponse(
                    new RedirectResponse($this->router->generate('extranet_portada'))
                );
            }
            else {
                $event->setResponse(
                    new RedirectResponse($this->router->generate('portada', array('ciudad' => $this->ciudad)))
                );
            }
            
            $event->stopPropagation();
        }
    }
}