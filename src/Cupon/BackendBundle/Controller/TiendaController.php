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
use Cupon\TiendaBundle\Entity\Tienda;
use Cupon\BackendBundle\Form\TiendaType;

/**
 * Tienda controller.
 *
 */
class TiendaController extends Controller
{
    /**
     * Lists all Tienda entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $slug = $this->getRequest()->getSession()->get('ciudad');
        $entities = $em->getRepository('CiudadBundle:Ciudad')->findTodasLasTiendas($slug);

        return $this->render('BackendBundle:Tienda:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Tienda entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TiendaBundle:Tienda')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se ha encontrado la tienda solicitada');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Tienda:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Tienda entity.
     *
     */
    public function newAction()
    {
        $entity = new Tienda();
        $form   = $this->createForm(new TiendaType(), $entity);

        return $this->render('BackendBundle:Tienda:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Tienda entity.
     *
     */
    public function createAction()
    {
        $entity  = new Tienda();
        $request = $this->getRequest();
        $form    = $this->createForm(new TiendaType(), $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_tienda_show', array('id' => $entity->getId())));

        }

        return $this->render('BackendBundle:Tienda:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Tienda entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TiendaBundle:Tienda')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se ha encontrado la tienda solicitada');
        }

        $editForm = $this->createForm(new TiendaType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Tienda:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Tienda entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TiendaBundle:Tienda')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se ha encontrado la tienda solicitada');
        }

        $editForm   = $this->createForm(new TiendaType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_tienda_edit', array('id' => $id)));
        }

        return $this->render('BackendBundle:Tienda:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Tienda entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('TiendaBundle:Tienda')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('No se ha encontrado la tienda solicitada');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('backend_tienda'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
