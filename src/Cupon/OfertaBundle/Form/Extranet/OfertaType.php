<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\OfertaBundle\Form\Extranet;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Formulario para crear y manipular entidades de tipo Oferta.
 * Como se utiliza en la extranet, algunas propiedades de la entidad
 * no se incluyen en el formulario.
 */
class OfertaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('descripcion')
            ->add('condiciones')
            ->add('foto', 'file', array('required' => false))
            ->add('precio', 'money')
            ->add('descuento', 'money')
            ->add('umbral')
        ;

        // El formulario es diferente según se utilice en la acción 'new' o en la acción 'edit'
        // Para determinar en qué acción estamos, se comprueba si el atributo `id` del objeto
        // es null, en cuyo caso estamos en la acción 'new'
        //
        // La acción `new` muestra un checkbox que no corresponde a ninguna propiedad de la entidad
        // del modelo. Se añade dinámicamente y se indica que no es parte del modelo (con la propiedad
        // `property_path`).
        //
        // También se añade dinámicamente un validador para comprobar que el checkbox añadido ha sido
        // activado y para mostrar un mensaje de error en caso contrario.
        if (null == $options['data']->getId()) {
            $builder->add('acepto', 'checkbox', array('mapped' => false));

            $builder->addValidator(new CallbackValidator(function(FormInterface $form) {
                if ($form["acepto"]->getData() == false) {
                    $form->addError(new FormError('Debes aceptar las condiciones indicadas antes de poder añadir una nueva oferta'));
                }
            }));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Cupon\OfertaBundle\Entity\Oferta',
        ));
    }

    public function getName()
    {
        return 'oferta_tienda';
    }
}
