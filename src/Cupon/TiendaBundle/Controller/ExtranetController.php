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
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Cupon\OfertaBundle\Entity\Oferta;
use Cupon\OfertaBundle\Form\Extranet\OfertaType;
use Cupon\TiendaBundle\Form\Extranet\TiendaType;

class ExtranetController extends Controller
{
    /**
     * Muestra el formulario de login
     */
    public function loginAction()
    {
        $peticion = $this->getRequest();
        $sesion = $peticion->getSession();

        $error = $peticion->attributes->get(
            SecurityContext::AUTHENTICATION_ERROR,
            $sesion->get(SecurityContext::AUTHENTICATION_ERROR)
        );

        return $this->render('TiendaBundle:Extranet:login.html.twig', array(
            'error' => $error
        ));
    }

    /**
     * Muestra la portada de la extranet de la tienda que está logueada en
     * la aplicación
     */
    public function portadaAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $tienda = $this->get('security.context')->getToken()->getUser();
        $ofertas = $em->getRepository('TiendaBundle:Tienda')->findOfertasRecientes($tienda->getId(), 50);

        return $this->render('TiendaBundle:Extranet:portada.html.twig', array(
            'ofertas' => $ofertas
        ));
    }

    /**
     * Muestra las ventas registradas para la oferta indicada
     *
     * @param string $id El id de la oferta para la que se buscan sus ventas
     */
    public function ofertaVentasAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $ventas = $em->getRepository('OfertaBundle:Oferta')->findVentasByOferta($id);

        return $this->render('TiendaBundle:Extranet:ventas.html.twig', array(
            'oferta' => $ventas[0]->getOferta(),
            'ventas' => $ventas
        ));
    }

    /**
     * Muestra el formulario para crear una nueva oferta y se encarga del
     * procesamiento de la información recibida y la creación de las nuevas
     * entidades de tipo Oferta
     */
    public function ofertaNuevaAction()
    {
        $peticion = $this->getRequest();

        $oferta = new Oferta();
        $formulario = $this->createForm(new OfertaType(), $oferta);

        if ($peticion->getMethod() == 'POST') {
           $formulario->bindRequest($peticion);

           if ($formulario->isValid()) {
               // Completar las propiedades de la oferta que una tienda no puede establecer
               $tienda = $this->get('security.context')->getToken()->getUser();
               $oferta->setCompras(0);
               $oferta->setRevisada(false);
               $oferta->setTienda($tienda);
               $oferta->setCiudad($tienda->getCiudad());

               // Copiar la foto subida y guardar la ruta
               $oferta->subirFoto($this->container->getParameter('cupon.directorio.imagenes'));

               $em = $this->getDoctrine()->getEntityManager();
               $em->persist($oferta);
               $em->flush();

               // Asignar el permiso necesario para que la tienda pueda modificar esta oferta
               $idObjeto  = ObjectIdentity::fromDomainObject($oferta);
               $idUsuario = UserSecurityIdentity::fromAccount($tienda);

               $acl = $this->get('security.acl.provider')->createAcl($idObjeto);
               $acl->insertObjectAce($idUsuario, MaskBuilder::MASK_OPERATOR);
               $this->get('security.acl.provider')->updateAcl($acl);

               return $this->redirect($this->generateUrl('extranet_portada'));
           }
       }

        return $this->render('TiendaBundle:Extranet:formulario.html.twig', array(
            'accion'     => 'crear',
            'formulario' => $formulario->createView()
        ));
    }

    /**
     * Muestra el formulario para editar una oferta y se encarga del
     * procesamiento de la información recibida y la modificación de los
     * datos de las entidades de tipo Oferta
     *
     * @param string $id El id de la oferta a modificar
     */
    public function ofertaEditarAction($id)
    {
        $peticion = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $oferta = $em->getRepository('OfertaBundle:Oferta')->find($id);

        if (!$oferta) {
            throw $this->createNotFoundException('La oferta indicada no está disponible');
        }

        // Comprobar que el usuario tiene permiso para editar esta oferta concreta
        if (false === $this->get('security.context')->isGranted('EDIT', $oferta)) {
            throw new AccessDeniedException();
        }

        // Una oferta sólo se puede modificar si todavía no ha sido revisada por los administradores
        if ($oferta->getRevisada()) {
            $this->get('session')->setFlash('error',
                'La oferta indicada no se puede modificar porque ya ha sido revisada por los administradores'
            );

            return $this->redirect($this->generateUrl('extranet_portada'));
        }

        $formulario = $this->createForm(new OfertaType(), $oferta);

        if ($peticion->getMethod() == 'POST') {
            // Guardar la ruta de la foto original de la oferta
            $fotoOriginal = $formulario->getData()->getFoto();

            $formulario->bindRequest($peticion);

            if ($formulario->isValid()) {
                // Si el usuario no ha modificado la foto, su valor actual es null
                if (null == $oferta->getFoto()) {
                    // Guardar la ruta original de la foto en la oferta y no hacer nada más
                    $oferta->setFoto($fotoOriginal);
                }
                // El usuario ha cambiado la foto
                else {
                    // Copiar la foto subida y guardar la nueva ruta
                    $oferta->subirFoto($this->container->getParameter('cupon.directorio.imagenes'));

                    // Borrar la foto anterior
                    unlink($this->container->getParameter('cupon.directorio.imagenes').$fotoOriginal);
                }

                $em->persist($oferta);
                $em->flush();

                return $this->redirect($this->generateUrl('extranet_portada'));
            }
        }

        return $this->render('TiendaBundle:Extranet:formulario.html.twig', array(
            'accion'     => 'editar',
            'oferta'     => $oferta,
            'formulario' => $formulario->createView()
        ));
    }

    /**
     * Muestra el formulario para editar los datos del perfil de la tienda que está
     * logueada en la aplicación. También se encarga de procesar la información y
     * guardar las modificaciones en la base de datos
     */
    public function perfilAction()
    {
        $peticion = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $tienda = $this->get('security.context')->getToken()->getUser();
        $formulario = $this->createForm(new TiendaType(), $tienda);

        if ($peticion->getMethod() == 'POST') {
            $passwordOriginal = $formulario->getData()->getPassword();

            $formulario->bindRequest($peticion);

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

                $this->get('session')->setFlash('info',
                    'Los datos de tu perfil se han actualizado correctamente'
                );

                return $this->redirect($this->generateUrl('extranet_portada'));
            }
        }

        return $this->render('TiendaBundle:Extranet:perfil.html.twig', array(
            'tienda'     => $tienda,
            'formulario' => $formulario->createView()
        ));
    }
}
