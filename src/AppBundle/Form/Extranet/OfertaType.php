<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Form\Extranet;

use AppBundle\Listener\OfertaTypeListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

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
            ->add('foto', 'Symfony\Component\Form\Extension\Core\Type\FileType', array('required' => false))
            ->add('precio', 'Symfony\Component\Form\Extension\Core\Type\MoneyType')
            ->add('descuento', 'Symfony\Component\Form\Extension\Core\Type\MoneyType')
            ->add('umbral')
            ->add('guardar', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array(
                'label' => 'Guardar cambios',
                'attr' => array('class' => 'boton'),
            ))
        ;

        if (true === $options['mostrar_condiciones']) {
            // Cuando se crea una oferta, se muestra un checkbox para aceptar las
            // condiciones de uso
            $builder->add('acepto', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array(
                'mapped' => false,
                'constraints' => new IsTrue(array(
                    'message' => 'Debes aceptar las condiciones indicadas antes de poder añadir una nueva oferta'
                )),
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Oferta',
            'mostrar_condiciones' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return 'oferta_tienda';
    }
}
