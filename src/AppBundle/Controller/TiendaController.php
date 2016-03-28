<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TiendaController extends Controller
{
    /**
     * @Route("/{ciudad}/tiendas/{tienda}", requirements={ "ciudad" = ".+" }, name="tienda_portada")
     * @Cache(smaxage="3600")
     *
     * Muestra la portada de cada tienda, que muestra su información y las
     * ofertas que ha publicado recientemente.
     *
     * @param Request $request
     * @param string  $ciudad  El slug de la ciudad donde se encuentra la tienda
     * @param string  $tienda  El slug de la tienda
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function portadaAction(Request $request, $ciudad, $tienda)
    {
        $em = $this->getDoctrine()->getManager();

        $ciudad = $em->getRepository('AppBundle:Ciudad')->findOneBySlug($ciudad);
        $tienda = $em->getRepository('AppBundle:Tienda')->findOneBy(array(
            'slug' => $tienda,
            'ciudad' => $ciudad->getId(),
        ));

        if (!$tienda) {
            throw $this->createNotFoundException('La tienda indicada no está disponible');
        }

        $ofertas = $em->getRepository('AppBundle:Tienda')->findUltimasOfertasPublicadas($tienda->getId());
        $cercanas = $em->getRepository('AppBundle:Tienda')->findCercanas(
            $tienda->getSlug(),
            $tienda->getCiudad()->getSlug()
        );

        $formato = $request->getRequestFormat();

        return $this->render('tienda/portada.'.$formato.'.twig', array(
            'tienda' => $tienda,
            'ofertas' => $ofertas,
            'cercanas' => $cercanas,
        ));
    }
}
