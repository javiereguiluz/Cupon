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
use Cupon\OfertaBundle\Entity\Oferta;
use Cupon\BackendBundle\Form\OfertaType;

/**
 * Oferta controller.
 *
 */
class OfertaController extends Controller
{
    /**
     * Lists all Oferta entities.
     *
     */
    public function indexAction()
    {
        // Si el usuario no ha seleccionado ninguna ciudad, seleccionar
        // la ciudad por defecto
        $sesion = $this->getRequest()->getSession();
        if (null == $slug = $sesion->get('ciudad')) {
            $slug = $this->container->getParameter('cupon.ciudad_por_defecto');
            $sesion->set('ciudad', $slug);
        }

        $em = $this->getDoctrine()->getEntityManager();
        $paginador = $this->get('ideup.simple_paginator');
        $paginador->setItemsPerPage(19);

        $entities  = $paginador->paginate(
            $em->getRepository('CiudadBundle:Ciudad')->queryTodasLasOfertas($slug)
        )->getResult();

        return $this->render('BackendBundle:Oferta:index.html.twig', array(
            'entities'  => $entities,
            'paginador' => $paginador
        ));
    }

    /**
     * Finds and displays a Oferta entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('OfertaBundle:Oferta')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se ha encontrado la oferta solicitada');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Oferta:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Oferta entity.
     *
     */
    public function newAction()
    {
        $entity = new Oferta();

        // Rellenar con valores adecuados algunas propiedades
        $em = $this->getDoctrine()->getEntityManager();

        $ciudad = $em->getRepository('CiudadBundle:Ciudad')->findOneBySlug(
            $this->getRequest()->getSession()->get('ciudad')
        );

        $entity->setCiudad($ciudad);
        $entity->setCompras(0);
        $entity->setUmbral(0);
        $entity->setFechaPublicacion(new \DateTime('now'));
        $entity->setFechaExpiracion(new \DateTime('tomorrow'));

        $form = $this->createForm(new OfertaType(), $entity);

        return $this->render('BackendBundle:Oferta:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Oferta entity.
     *
     */
    public function createAction()
    {
        $entity  = new Oferta();
        $request = $this->getRequest();
        $form    = $this->createForm(new OfertaType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_oferta_show', array('id' => $entity->getId())));
        }

        return $this->render('BackendBundle:Oferta:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Oferta entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('OfertaBundle:Oferta')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se ha encontrado la oferta solicitada');
        }

        $editForm = $this->createForm(new OfertaType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Oferta:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Oferta entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('OfertaBundle:Oferta')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se ha encontrado la oferta solicitada');
        }

        $editForm   = $this->createForm(new OfertaType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_oferta_edit', array('id' => $id)));
        }

        return $this->render('BackendBundle:Oferta:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Oferta entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('OfertaBundle:Oferta')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('No se ha encontrado la oferta solicitada');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('backend_oferta'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
