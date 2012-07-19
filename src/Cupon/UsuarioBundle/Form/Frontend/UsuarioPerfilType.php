<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\UsuarioBundle\Form\Frontend;

use Cupon\UsuarioBundle\Form\Frontend\UsuarioRegistroType;

/**
 * Formulario para editar el perfil de los usuarios registrados.
 */
class UsuarioPerfilType extends UsuarioRegistroType
{
    /**
     * El formulario para editar el perfil utiliza una validación diferente a
     * la del formulario para darse de alta (escribir la contraseña por
     * ejemplo no es obligatorio)
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'validation_groups' => array('default')
        );
    }
}
