<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\OfertaBundle\Listener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;

/**
 * Este listener se emplea para añadir un validador propio que compruebe los campos
 * del formulario que no se corresponden con ninguna propiedad de la entidad.
 */
class OfertaTypeListener
{
    /**
     * Valida que el usuario ha activado el checkbox para aceptar las condiciones de uso.
     *
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $formulario = $event->getForm();

        if (false == $formulario->get('acepto')->getData()) {
            $formulario->get('acepto')->addError(new FormError(
                'Debes aceptar las condiciones indicadas antes de poder añadir una nueva oferta'
            ));
        }
    }
}
