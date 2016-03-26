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
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Validator\Constraints\IsTrue;

class ExtranetController extends Controller
{
    /**
     * @Route("/login", name="extranet_login")
     * Muestra el formulario de login
     */
    public function loginAction(Request $request)
    {
        $sesion = $request->getSession();

        $error = $request->attributes->get(
            SecurityContext::AUTHENTICATION_ERROR,
            $sesion->get(SecurityContext::AUTHENTICATION_ERROR)
        );

        return $this->render('extranet/login.html.twig', array(
            'error' => $error,
        ));
    }

    /**
     * @Route("/login_check", name="extranet_login_check")
     */
    public function loginCheckAction()
    {
        // el "login check" lo hace Symfony automáticamente
    }

    /**
     * @Route("/logout", name="extranet_logout")
     */
    public function logoutAction()
    {
        // el logout lo hace Symfony automáticamente
    }

    /**
     * @Route("/", name="extranet_portada")
     * Muestra la portada de la extranet de la tienda que está logueada en
     * la aplicación
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
     * Muestra las ventas registradas para la oferta indicada
     *
     * @param string $id El id de la oferta para la que se buscan sus ventas
     */
    public function ofertaVentasAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $ventas = $em->getRepository('AppBundle:Oferta')->findVentasByOferta($id);

        return $this->render('extranet/ventas.html.twig', array(
            'oferta' => $ventas[0]->getOferta(),
            'ventas' => $ventas,
        ));
    }

    /**
     * @Route("/oferta/nueva", name="extranet_oferta_nueva")
     * Muestra el formulario para crear una nueva oferta y se encarga del
     * procesamiento de la información recibida y la creación de las nuevas
     * entidades de tipo Oferta
     */
    public function ofertaNuevaAction(Request $request)
    {
        $oferta = new Oferta();
        $formulario = $this->createForm(new OfertaType(), $oferta);

        // Cuando se crea una oferta, se muestra un checkbox para aceptar las
        // condiciones de uso. Este campo de formulario no se corresponde con
        // ninguna propiedad de la entidad, por lo que se añade dinámicamente
        // al formulario
        $formulario->add('acepto', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
            'mapped' => false,
            'constraints' => new IsTrue(array(
                'message' => 'Debes aceptar las condiciones indicadas antes de poder añadir una nueva oferta'
            )),
        ));

        $formulario->handleRequest($request);

        if ($formulario->isValid()) {
            // Completar las propiedades de la oferta que una tienda no puede establecer
            $tienda = $this->get('security.token_storage')->getToken()->getUser();
            $oferta->setCompras(0);
            $oferta->setRevisada(false);
            $oferta->setTienda($tienda);
            $oferta->setCiudad($tienda->getCiudad());

            // Copiar la foto subida y guardar la ruta
            $oferta->subirFoto($this->container->getParameter('cupon.directorio.imagenes'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($oferta);
            $em->flush();

            return $this->redirect($this->generateUrl('extranet_portada'));
        }

        return $this->render('extranet/formulario.html.twig', array(
            'accion' => 'crear',
            'formulario' => $formulario->createView(),
        ));
    }

    /**
     * @Route("/oferta/editar/{id}", requirements={ "ciudad" = ".+" }, name="extranet_oferta_editar")
     * Muestra el formulario para editar una oferta y se encarga del
     * procesamiento de la información recibida y la modificación de los
     * datos de las entidades de tipo Oferta
     *
     * @param string $id El id de la oferta a modificar
     */
    public function ofertaEditarAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $oferta = $em->getRepository('AppBundle:Oferta')->find($id);

        if (!$oferta) {
            throw $this->createNotFoundException('La oferta indicada no está disponible');
        }

        $this->denyAccessUnlessGranted('ROLE_EDITAR_OFERTA', $oferta);

        // Una oferta sólo se puede modificar si todavía no ha sido revisada por los administradores
        if ($oferta->getRevisada()) {
            $this->get('session')->getFlashBag()->add('error',
                'La oferta indicada no se puede modificar porque ya ha sido revisada por los administradores'
            );

            return $this->redirect($this->generateUrl('extranet_portada'));
        }

        $formulario = $this->createForm(new OfertaType(), $oferta);

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

            $em->persist($oferta);
            $em->flush();

            return $this->redirect($this->generateUrl('extranet_portada'));
        }

        return $this->render('extranet/formulario.html.twig', array(
            'accion' => 'editar',
            'oferta' => $oferta,
            'formulario' => $formulario->createView(),
        ));
    }

    /**
     * @Route("/perfil", name="extranet_perfil")
     * Muestra el formulario para editar los datos del perfil de la tienda que está
     * logueada en la aplicación. También se encarga de procesar la información y
     * guardar las modificaciones en la base de datos
     */
    public function perfilAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $tienda = $this->get('security.token_storage')->getToken()->getUser();
        $formulario = $this->createForm(new TiendaType(), $tienda);

        $passwordOriginal = $formulario->getData()->getPassword();

        $formulario->handleRequest($request);

        if ($formulario->isValid()) {
            // Si el usuario no ha cambiado el password, su valor es null después de
            // hacer el ->bindRequest(), por lo que hay que recuperar el valor original
            if (null == $tienda->getPassword()) {
                $tienda->setPassword($passwordOriginal);
            }
            // Si el usuario ha cambiado su password, hay que codificarlo antes de guardarlo
            else {
                $encoder = $this->get('security.encoder_factory')->getEncoder($tienda);
                $passwordCodificado = $encoder->encodePassword(
                    $tienda->getPassword(),
                    $tienda->getSalt()
                );
                $tienda->setPassword($passwordCodificado);
            }

            $em->persist($tienda);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info',
                'Los datos de tu perfil se han actualizado correctamente'
            );

            return $this->redirect($this->generateUrl('extranet_portada'));
        }

        return $this->render('extranet/perfil.html.twig', array(
            'tienda' => $tienda,
            'formulario' => $formulario->createView(),
        ));
    }
}
