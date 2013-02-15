<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\TiendaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * Muestra la portada de cada tienda, que muestra su información y las
     * ofertas que ha publicado recientemente
     *
     * @param string $ciudad El slug de la ciudad donde se encuentra la tienda
     * @param string $tienda El slug de la tienda
     */
    public function portadaAction($ciudad, $tienda)
    {
        $em = $this->getDoctrine()->getManager();

        $ciudad = $em->getRepository('CiudadBundle:Ciudad')->findOneBySlug($ciudad);
        $tienda = $em->getRepository('TiendaBundle:Tienda')->findOneBy(array(
            'slug' => $tienda,
            'ciudad' => $ciudad->getId()
        ));

        if (!$tienda) {
            throw $this->createNotFoundException('La tienda indicada no está disponible');
        }

        $ofertas = $em->getRepository('TiendaBundle:Tienda')->findUltimasOfertasPublicadas($tienda->getId());
        $cercanas = $em->getRepository('TiendaBundle:Tienda')->findCercanas(
            $tienda->getSlug(),
            $tienda->getCiudad()->getSlug()
        );

        $formato = $this->get('request')->getRequestFormat();
        $respuesta = $this->render('TiendaBundle:Default:portada.'.$formato.'.twig', array(
            'tienda'   => $tienda,
            'ofertas'  => $ofertas,
            'cercanas' => $cercanas
        ));

        $respuesta->setSharedMaxAge(3600);

        return $respuesta;
    }
}
