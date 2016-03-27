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
use AppBundle\Form\Extranet\OfertaType;
use AppBundle\Form\Extranet\TiendaType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExtranetController extends Controller
{
    /**
     * @Route("/login", name="extranet_login")
     * Muestra el formulario de login
     */
    public function loginAction(Request $request)
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
     * Muestra la portada de la extranet de la tienda que está logueada en
     * la aplicación
     *
     * @Route("/", name="extranet_portada")
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
     * Muestra las ventas registradas para la oferta indicada
     *
     * @Route("/oferta/ventas/{id}", name="extranet_oferta_ventas")
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
     * Muestra el formulario para crear una nueva oferta y se encarga del
     * procesamiento de la información recibida y la creación de las nuevas
     * entidades de tipo Oferta.
     *
     * @Route("/oferta/nueva", name="extranet_oferta_nueva")
     */
    public function ofertaNuevaAction(Request $request)
    {
        $oferta = new Oferta();
        $formulario = $this->createForm('AppBundle\Form\Extranet\OfertaType', $oferta, array('mostrar_condiciones' => true));
        $formulario->handleRequest($request);

        if ($formulario->isValid()) {
            // Completar las propiedades de la oferta que una tienda no puede establecer
            $tienda = $this->get('security.token_storage')->getToken()->getUser();
            $oferta->setTienda($tienda);
            $oferta->setCiudad($tienda->getCiudad());

            // Copiar la foto subida y guardar la ruta
            $oferta->subirFoto($this->container->getParameter('cupon.directorio.imagenes'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($oferta);
            $em->flush();

            return $this->redirectToRoute('extranet_portada');
        }

        return $this->render('extranet/formulario.html.twig', array(
            'accion' => 'crear',
            'formulario' => $formulario->createView(),
        ));
    }

    /**
     * Muestra el formulario para editar una oferta y se encarga del
     * procesamiento de la información recibida y la modificación de los
     * datos de las entidades de tipo Oferta
     *
     * @Route("/oferta/editar/{id}", requirements={ "ciudad" = ".+" }, name="extranet_oferta_editar")
     */
    public function ofertaEditarAction(Request $request, Oferta $oferta)
    {
        $this->denyAccessUnlessGranted('ROLE_EDITAR_OFERTA', $oferta);

        // Una oferta sólo se puede modificar si todavía no ha sido revisada por los administradores
        if ($oferta->getRevisada()) {
            $this->addFlash('error', 'La oferta indicada no se puede modificar porque ya ha sido revisada por los administradores');

            return $this->redirectToRoute('extranet_portada');
        }

        $formulario = $this->createForm('AppBundle\Form\Extranet\OfertaType', $oferta);

        // Guardar la ruta de la foto original de la oferta
        $rutaFotoOriginal = $formulario->getData()->getRutaFoto();

        $formulario->handleRequest($request);

        if ($formulario->isValid()) {
            // Si el usuario no ha modificado la foto, su valor actual es null
            if (null == $oferta->getFoto()) {
                // Guardar la ruta original de la foto en la oferta y no hacer nada más
                $oferta->setRutaFoto($rutaFotoOriginal);
            }
            // El usuario ha cambiado la foto
            else {
                // Copiar la foto subida y guardar la nueva ruta
                $oferta->subirFoto($this->container->getParameter('cupon.directorio.imagenes'));

                // Borrar la foto anterior
                if (!empty($rutaFotoOriginal)) {
                    $fs = new Filesystem();
                    $fs->remove($this->container->getParameter('cupon.directorio.imagenes').$rutaFotoOriginal);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($oferta);
            $em->flush();

            return $this->redirectToRoute('extranet_portada');
        }

        return $this->render('extranet/formulario.html.twig', array(
            'accion' => 'editar',
            'oferta' => $oferta,
            'formulario' => $formulario->createView(),
        ));
    }

    /**
     * Muestra el formulario para editar los datos del perfil de la tienda que está
     * logueada en la aplicación. También se encarga de procesar la información y
     * guardar las modificaciones en la base de datos.
     *
     * @Route("/perfil", name="extranet_perfil")
     */
    public function perfilAction(Request $request)
    {
        $tienda = $this->get('security.token_storage')->getToken()->getUser();
        $formulario = $this->createForm('AppBundle\Form\Extranet\TiendaType', $tienda);

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
