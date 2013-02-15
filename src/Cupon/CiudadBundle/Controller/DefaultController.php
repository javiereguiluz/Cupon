<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\CiudadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    /**
     * Busca todas las ciudades disponibles en la base de datos y pasa la lista
     * a una plantilla muy sencilla que simplemente muestra una lista desplegable
     * para seleccionar la ciudad activa.
     *
     * @param string $ciudad El slug de la ciudad seleccionada
     */
    public function listaCiudadesAction($ciudad = null)
    {
        $em = $this->getDoctrine()->getManager();
        $ciudades = $em->getRepository('CiudadBundle:Ciudad')->findListaCiudades();

        return $this->render('CiudadBundle:Default:listaCiudades.html.twig', array(
            'ciudadActual' => $ciudad,
            'ciudades'     => $ciudades
        ));
    }

    /**
     * Cambia la ciudad activa por la que se indica. En la parte frontal de la
     * aplicación esto simplemente significa que se le redirige al usuario a la
     * portada de la nueva ciudad seleccionada.
     *
     * @param string $ciudad El slug de la ciudad a la que se cambia
     */
    public function cambiarAction($ciudad)
    {
        return new RedirectResponse($this->generateUrl('portada', array('ciudad' => $ciudad)));
    }

    /**
     * Muestra las ofertas más recientes de la ciudad indicada
     *
     * @param string $ciudad El slug de la ciudad
     */
    public function recientesAction($ciudad)
    {
        $em = $this->getDoctrine()->getManager();

        $ciudad = $em->getRepository('CiudadBundle:Ciudad')->findOneBySlug($ciudad);
        if (!$ciudad) {
            throw $this->createNotFoundException('La ciudad indicada no está disponible');
        }

        $cercanas = $em->getRepository('CiudadBundle:Ciudad')->findCercanas($ciudad->getId());
        $ofertas  = $em->getRepository('OfertaBundle:Oferta')->findRecientes($ciudad->getId());

        $formato = $this->get('request')->getRequestFormat();
        $respuesta = $this->render('CiudadBundle:Default:recientes.'.$formato.'.twig', array(
            'ciudad'   => $ciudad,
            'cercanas' => $cercanas,
            'ofertas'  => $ofertas
        ));

        $respuesta->setSharedMaxAge(3600);

        return $respuesta;
    }
}
