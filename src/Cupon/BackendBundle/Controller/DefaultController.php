<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    /**
     * Permite al administrador del backend cambiar la ciudad activa
     * por cualquier otra ciudad de la aplicación.
     *
     * @param string $ciudad Slug de la ciudad a la que se cambia
     */
    public function ciudadCambiarAction($ciudad)
    {
        $this->getRequest()->getSession()->set('ciudad', $ciudad);

        // Cuando un usuario cambia la ciudad activa, se le redirige a la
        // misma página que estaba viendo, pero con los datos de la nueva ciudad
        $dondeEstaba = $this->getRequest()->server->get('HTTP_REFERER');

        return new RedirectResponse($dondeEstaba, 302);
    }
}
