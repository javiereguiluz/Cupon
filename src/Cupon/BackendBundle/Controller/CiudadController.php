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
use Cupon\CiudadBundle\Entity\Ciudad;
use Cupon\BackendBundle\Form\CiudadType;

/**
 * Ciudad controller.
 *
 */
class CiudadController extends Controller
{
    /**
     * Lists all Ciudad entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CiudadBundle:Ciudad')->findAll();

        return $this->render('BackendBundle:Ciudad:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Ciudad entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CiudadBundle:Ciudad')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se ha encontrado la ciudad solicitada');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Ciudad:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Ciudad entity.
     *
     */
    public function newAction()
    {
        $entity = new Ciudad();
        $form   = $this->createForm(new CiudadType(), $entity);

        return $this->render('BackendBundle:Ciudad:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Ciudad entity.
     *
     */
    public function createAction()
    {
        $entity  = new Ciudad();
        $request = $this->getRequest();
        $form    = $this->createForm(new CiudadType(), $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_ciudad_show', array('id' => $entity->getId())));
        }

        return $this->render('BackendBundle:Ciudad:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Ciudad entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CiudadBundle:Ciudad')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se ha encontrado la ciudad solicitada');
        }

        $editForm = $this->createForm(new CiudadType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Ciudad:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Ciudad entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CiudadBundle:Ciudad')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se ha encontrado la ciudad solicitada');
        }

        $editForm   = $this->createForm(new CiudadType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_ciudad_edit', array('id' => $id)));
        }

        return $this->render('BackendBundle:Ciudad:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Ciudad entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CiudadBundle:Ciudad')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('No se ha encontrado la ciudad solicitada');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('backend_ciudad'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
