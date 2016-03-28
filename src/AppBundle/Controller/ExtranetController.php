<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Oferta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExtranetController extends Controller
{
    /**
     * @Route("/login", name="extranet_login")
     *
     * Muestra el formulario de login
     *
     * @return Response
     */
    public function loginAction()
    {
        $authUtils = $this->get('security.authentication_utils');

        return $this->render('extranet/login.html.twig', array(
            'last_username' => $authUtils->getLastUsername(),
            'error' => $authUtils->getLastAuthenticationError(),
        ));
    }

    /**
     * @Route("/login_check", name="extranet_login_check")
     */
    public function loginCheckAction()
    {
        // el "login check" lo hace Symfony automáticamente, pero es necesario
        // definir una ruta /login_check. Por eso existe este método vacío.
    }

    /**
     * @Route("/logout", name="extranet_logout")
     */
    public function logoutAction()
    {
        // el logout lo hace Symfony automáticamente, pero es necesario
        // definir una ruta /logout. Por eso existe este método vacío.
    }

    /**
     * @Route("/", name="extranet_portada")
     *
     * Muestra la portada de la extranet de la tienda que está logueada en
     * la aplicación.
     *
     * @return Response
     */
    public function portadaAction()
    {
        $em = $this->getDoctrine()->getManager();

        $tienda = $this->get('security.token_storage')->getToken()->getUser();
        $ofertas = $em->getRepository('AppBundle:Tienda')->findOfertasRecientes($tienda->getId(), 50);

        return $this->render('extranet/portada.html.twig', array(
            'ofertas' => $ofertas,
        ));
    }

    /**
     * @Route("/oferta/ventas/{id}", name="extranet_oferta_ventas")
     *
     * Muestra las ventas registradas para la oferta indicada.
     *
     * @param Oferta $oferta
     *
     * @return Response
     */
    public function ofertaVentasAction(Oferta $oferta)
    {
        $em = $this->getDoctrine()->getManager();
        $ventas = $em->getRepository('AppBundle:Oferta')->findVentasByOferta($oferta->getId());

        return $this->render('extranet/ventas.html.twig', array(
            'oferta' => $oferta,
            'ventas' => $ventas,
        ));
    }

    /**
     * @Route("/oferta/nueva", name="extranet_oferta_nueva")
     *
     * Muestra el formulario para crear una nueva oferta y se encarga del
     * procesamiento de la información recibida y la creación de las nuevas
     * entidades de tipo Oferta.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function ofertaNuevaAction(Request $request)
    {
        $tienda = $this->get('security.token_storage')->getToken()->getUser();

        $oferta = Oferta::crearParaTienda($tienda);
        $formulario = $this->createForm('AppBundle\Form\OfertaType', $oferta, array('mostrar_condiciones' => true));
        $formulario->handleRequest($request);

        if ($formulario->isValid()) {
            $this->get('app.manager.oferta_manager')->guardar($oferta);

            return $this->redirectToRoute('extranet_portada');
        }

        return $this->render(
            'extranet/oferta.html.twig', array(
            'accion' => 'crear',
            'formulario' => $formulario->createView(),
        ));
    }

    /**
     * @Route("/oferta/editar/{id}", requirements={ "ciudad" = ".+" }, name="extranet_oferta_editar")
     *
     * Muestra el formulario para editar una oferta y se encarga del
     * procesamiento de la información recibida y la modificación de los
     * datos de las entidades de tipo Oferta.
     *
     * @param Request $request
     * @param Oferta  $oferta
     *
     * @return RedirectResponse|Response
     *
     * @throws AccessDeniedException
     */
    public function ofertaEditarAction(Request $request, Oferta $oferta)
    {
        $this->denyAccessUnlessGranted('ROLE_EDITAR_OFERTA', $oferta);

        // Una oferta sólo se puede modificar si todavía no ha sido revisada por los administradores
        if ($oferta->getRevisada()) {
            $this->addFlash('error', 'La oferta indicada no se puede modificar porque ya ha sido revisada por los administradores');

            return $this->redirectToRoute('extranet_portada');
        }

        $formulario = $this->createForm('AppBundle\Form\OfertaType', $oferta);
        $formulario->handleRequest($request);

        if ($formulario->isValid()) {
            $this->get('app.manager.oferta_manager')->guardar($oferta);

            return $this->redirectToRoute('extranet_portada');
        }

        return $this->render(
            'extranet/oferta.html.twig', array(
            'accion' => 'editar',
            'oferta' => $oferta,
            'formulario' => $formulario->createView(),
        ));
    }

    /**
     * @Route("/perfil", name="extranet_perfil")
     *
     * Muestra el formulario para editar los datos del perfil de la tienda que está
     * logueada en la aplicación. También se encarga de procesar la información y
     * guardar las modificaciones en la base de datos.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function perfilAction(Request $request)
    {
        $tienda = $this->get('security.token_storage')->getToken()->getUser();
        $formulario = $this->createForm('AppBundle\Form\TiendaType', $tienda);

        $formulario->handleRequest($request);

        if ($formulario->isValid()) {
            $this->get('app.manager.tienda_manager')->guardar($tienda);
            $this->addFlash('info', 'Los datos de tu perfil se han actualizado correctamente');

            return $this->redirectToRoute('extranet_portada');
        }

        return $this->render('extranet/perfil.html.twig', array(
            'tienda' => $tienda,
            'formulario' => $formulario->createView(),
        ));
    }
}
