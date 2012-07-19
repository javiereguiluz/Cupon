<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\OfertaBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{
    /**
     * Ejemplo de cómo añadir un nuevo formato a la aplicación
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $event->getRequest()->setFormat('pdf', 'application/pdf');
    }
}
