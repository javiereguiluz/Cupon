<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Usuario;
use AppBundle\Entity\Venta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/usuario")
 */
class UsuarioController extends Controller
{
    /**
     * @Route("/login", name="usuario_login")
     *
     * Muestra el formulario de login
     *
     * @return Response
     */
    public function loginAction()
    {
        $authUtils = $this->get('security.authentication_utils');

        return $this->render('usuario/login.html.twig', array(
            'last_username' => $authUtils->getLastUsername(),
            'error' => $authUtils->getLastAuthenticationError(),
        ));
    }

    /**
     * @Route("/login_check", name="usuario_login_check")
     */
    public function loginCheckAction()
    {
        // el "login check" lo hace Symfony automáticamente
    }

    /**
     * @Route("/logout", name="usuario_logout")
     */
    public function logoutAction()
    {
        // el logout lo hace Symfony automáticamente
    }

    /**
     * @Cache(maxage="30")
     *
     * Muestra la caja de login que se incluye en el lateral de la mayoría de páginas del sitio web.
     * Esta caja se transforma en información y enlaces cuando el usuario se loguea en la aplicación.
     * La respuesta se marca como privada para que no se añada a la cache pública. El trozo de plantilla
     * que llama a esta función se sirve a través de ESI.
     *
     * @return Response
     */
    public function cajaLoginAction()
    {
        $usuario = $this->get('security.token_storage')->getToken()->getUser();

        return $this->render('usuario/_caja_login.html.twig', array(
            'usuario' => $usuario,
        ));
    }

    /**
     * @Route("/registro", name="usuario_registro")
     *
     * Muestra el formulario para que se registren los nuevos usuarios. Además
     * se encarga de procesar la información y de guardar la información en la base de datos.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function registroAction(Request $request)
    {
        $usuario = new Usuario();
        $formulario = $this->createForm('AppBundle\Form\UsuarioType', $usuario, array(
            'accion' => 'crear_usuario',
            'validation_groups' => array('default', 'registro'),
        ));
        $formulario->handleRequest($request);

        if ($formulario->isValid()) {
            $this->get('app.manager.usuario_manager')->guardar($usuario);
            $this->get('app.manager.usuario_manager')->loguear($usuario);

            $this->addFlash('info', '¡Enhorabuena! Te has registrado correctamente en Cupon');

            return $this->redirectToRoute('portada', array('ciudad' => $usuario->getCiudad()->getSlug()));
        }

        return $this->render('usuario/registro.html.twig', array(
            'formulario' => $formulario->createView(),
        ));
    }

    /**
     * @Route("/perfil", name="usuario_perfil")
     *
     * Muestra el formulario con toda la información del perfil del usuario logueado.
     * También permite modificar la información y guarda los cambios en la base de datos.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function perfilAction(Request $request)
    {
        $usuario = $this->get('security.token_storage')->getToken()->getUser();
        $formulario = $this->createForm('AppBundle\Form\UsuarioType', $usuario);

        $formulario->handleRequest($request);

        if ($formulario->isValid()) {
            $this->get('app.manager.usuario_manager')->guardar($usuario);
            $this->addFlash('info', 'Los datos de tu perfil se han actualizado correctamente');

            return $this->redirectToRoute('usuario_perfil');
        }

        return $this->render('usuario/perfil.html.twig', array(
            'usuario' => $usuario,
            'formulario' => $formulario->createView(),
        ));
    }

    /**
     * @Route("/compras", name="usuario_compras")
     *
     * Muestra todas las compras del usuario logueado.
     *
     * @return Response
     */
    public function comprasAction()
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $this->get('security.token_storage')->getToken()->getUser();

        $cercanas = $em->getRepository('AppBundle:Ciudad')->findCercanas(
            $usuario->getCiudad()->getId()
        );

        $compras = $em->getRepository('AppBundle:Usuario')->findTodasLasCompras($usuario->getId());

        return $this->render('usuario/compras.html.twig', array(
            'compras' => $compras,
            'cercanas' => $cercanas,
        ));
    }

    /**
     * @Route("/{ciudad}/ofertas/{slug}/comprar", name="comprar")
     * @Security("is_granted('ROLE_USUARIO')")
     *
     * Registra una nueva compra de la oferta indicada por parte del usuario logueado.
     *
     * @param Request $request
     * @param string  $ciudad  El slug de la ciudad a la que pertenece la oferta
     * @param string  $slug    El slug de la oferta
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function comprarAction(Request $request, $ciudad, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $this->get('security.token_storage')->getToken()->getUser();

        $ciudad = $em->getRepository('AppBundle:Ciudad')->findOneBySlug($ciudad);
        if (!$ciudad) {
            throw $this->createNotFoundException('La ciudad indicada no está disponible');
        }

        $oferta = $em->getRepository('AppBundle:Oferta')->findOneBy(array('ciudad' => $ciudad->getId(), 'slug' => $slug));
        if (!$oferta) {
            throw $this->createNotFoundException('La oferta indicada no está disponible');
        }

        // Un mismo usuario no puede comprar dos veces la misma oferta
        $venta = $em->getRepository('AppBundle:Venta')->findOneBy(array(
            'oferta' => $oferta->getId(),
            'usuario' => $usuario->getId(),
        ));

        if (null !== $venta) {
            $this->addFlash('error', sprintf('No puedes volver a comprar esta oferta (la compraste el %s)', $venta->getFecha()->format('d/m/Y')));

            return $this->redirect($request->headers->get('Referer', $this->generateUrl('portada')));
        }

        $this->get('app.manager.oferta_manager')->comprar($oferta, $usuario);

        return $this->render('usuario/comprar.html.twig', array(
            'oferta' => $oferta,
            'usuario' => $usuario,
        ));
    }
}
