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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @Route("/usuario")
 */
class UsuarioController extends Controller
{
    /**
     * @Route("/login", name="usuario_login")
     * Muestra el formulario de login
     */
    public function loginAction(Request $request)
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
     * Muestra la caja de login que se incluye en el lateral de la mayoría de páginas del sitio web.
     * Esta caja se transforma en información y enlaces cuando el usuario se loguea en la aplicación.
     * La respuesta se marca como privada para que no se añada a la cache pública. El trozo de plantilla
     * que llama a esta función se sirve a través de ESI
     *
     * @Cache(maxage="30")
     *
     * @param string $id El valor del bloque `id` de la plantilla,
     *                   que coincide con el valor del atributo `id` del elemento <body>
     *
     * @return Response
     */
    public function cajaLoginAction($id = '')
    {
        $usuario = $this->get('security.token_storage')->getToken()->getUser();

        return $this->render(
            'usuario/caja_login.html.twig', array(
            'id' => $id,
            'usuario' => $usuario,
        ));
    }
    /**
     * Muestra el formulario para que se registren los nuevos usuarios. Además
     * se encarga de procesar la información y de guardar la información en la base de datos
     *
     * @Route("/registro", name="usuario_registro")
     */
    public function registroAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $usuario = new Usuario();
        $formulario = $this->createForm('AppBundle\Form\Frontend\UsuarioRegistroType', $usuario);
        $formulario->handleRequest($request);

        if ($formulario->isValid()) {
            $this->get('app.manager.usuario_manager')->guardar($usuario);
            $this->get('app.manager.usuario_manager')->loguear($usuario);

            // Crear un mensaje flash para notificar al usuario que se ha registrado correctamente
            $this->addFlash('info', '¡Enhorabuena! Te has registrado correctamente en Cupon');

            return $this->redirectToRoute('portada', array('ciudad' => $usuario->getCiudad()->getSlug()));
        }

        return $this->render('usuario/registro.html.twig', array(
            'formulario' => $formulario->createView(),
        ));
    }

    /**
     * @Route("/perfil", name="usuario_perfil")
     * Muestra el formulario con toda la información del perfil del usuario logueado.
     * También permite modificar la información y guarda los cambios en la base de datos
     */
    public function perfilAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $usuario = $this->get('security.token_storage')->getToken()->getUser();
        $formulario = $this->createForm('AppBundle\Form\Frontend\UsuarioPerfilType', $usuario);
        $passwordOriginal = $formulario->getData()->getPassword();

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
     * Muestra todas las compras del usuario logueado
     */
    public function comprasAction()
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $this->get('security.token_storage')->getToken()->getUser();

        $cercanas = $em->getRepository('AppBundle:Ciudad')->findCercanas(
            $usuario->getCiudad()->getId()
        );

        $compras = $em->getRepository('UsuarioBundle:Usuario')->findTodasLasCompras($usuario->getId());

        return $this->render('usuario/compras.html.twig', array(
            'compras' => $compras,
            'cercanas' => $cercanas,
        ));
    }

    /**
     * Registra una nueva compra de la oferta indicada por parte del usuario logueado
     *
     * @Route("/{ciudad}/ofertas/{slug}/comprar", name="comprar")
     *
     * @param string $ciudad El slug de la ciudad a la que pertenece la oferta
     * @param string $slug   El slug de la oferta
     *
     * @return Response
     */
    public function comprarAction(Request $request, $ciudad, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $usuario = $this->get('security.token_storage')->getToken()->getUser();

        // Solo pueden comprar los usuarios registrados y logueados
        if (null === $usuario || !$this->get('security.authorization_checker')->isGranted('ROLE_USUARIO')) {
            $this->addFlash('info', 'Antes de comprar debes registrarte o conectarte con tu usuario y contraseña.');

            return $this->redirectToRoute('usuario_login');
        }

        // Comprobar que existe la ciudad indicada
        $ciudad = $em->getRepository('AppBundle:Ciudad')->findOneBySlug($ciudad);
        if (!$ciudad) {
            throw $this->createNotFoundException('La ciudad indicada no está disponible');
        }

        // Comprobar que existe la oferta indicada
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
            $fechaVenta = $venta->getFecha();

            $formateador = \IntlDateFormatter::create(
                $this->get('translator')->getLocale(),
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::NONE
            );

            $this->addFlash('error', 'No puedes volver a comprar la misma oferta (la compraste el '.$formateador->format($fechaVenta).').');

            return $this->redirect(
                $request->headers->get('Referer', $this->generateUrl('portada'))
            );
        }

        // Guardar la nueva venta e incrementar el contador de compras de la oferta
        $venta = new Venta();

        $venta->setOferta($oferta);
        $venta->setUsuario($usuario);
        $venta->setFecha(new \DateTime());

        $em->persist($venta);

        $oferta->setCompras($oferta->getCompras() + 1);

        $em->flush();

        return $this->render('usuario/comprar.html.twig', array(
            'oferta' => $oferta,
            'usuario' => $usuario,
        ));
    }
}
