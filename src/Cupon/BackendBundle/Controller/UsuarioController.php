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
use Cupon\UsuarioBundle\Entity\Usuario;
use Cupon\BackendBundle\Form\UsuarioType;

/**
 * Usuario controller.
 *
 */
class UsuarioController extends Controller
{
    /**
     * Lists all Usuario entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $paginador = $this->get('ideup.simple_paginator');

        $slug = $this->getRequest()->getSession()->get('ciudad');

        $entities  = $paginador->paginate(
            $em->getRepository('CiudadBundle:Ciudad')->queryTodosLosUsuarios($slug)
        )->getResult();

        return $this->render('BackendBundle:Usuario:index.html.twig', array(
            'entities'  => $entities,
            'paginador' => $paginador
        ));
    }

    /**
     * Finds and displays a Usuario entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UsuarioBundle:Usuario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se ha encontrado el usuario solicitado');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Usuario:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Usuario entity.
     *
     */
    public function newAction()
    {
        $entity = new Usuario();
        $form   = $this->createForm(new UsuarioType(), $entity);

        return $this->render('BackendBundle:Usuario:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Usuario entity.
     *
     */
    public function createAction()
    {
        $entity  = new Usuario();
        $request = $this->getRequest();
        $form    = $this->createForm(new UsuarioType(), $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_usuario_show', array('id' => $entity->getId())));

        }

        return $this->render('BackendBundle:Usuario:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Usuario entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UsuarioBundle:Usuario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se ha encontrado el usuario solicitado');
        }

        $editForm = $this->createForm(new UsuarioType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Usuario:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Usuario entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UsuarioBundle:Usuario')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('No se ha encontrado el usuario solicitado');
        }

        $editForm   = $this->createForm(new UsuarioType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('backend_usuario_edit', array('id' => $id)));
        }

        return $this->render('BackendBundle:Usuario:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Usuario entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('UsuarioBundle:Usuario')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('No se ha encontrado el usuario solicitado');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('backend_usuario'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
