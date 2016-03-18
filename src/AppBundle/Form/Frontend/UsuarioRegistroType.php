<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Form\Frontend;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulario para crear entidades de tipo Usuario cuando los usuarios se
 * registran en el sitio.
 * Como se utiliza en la parte pública del sitio, algunas propiedades de
 * la entidad no se incluyen en el formulario.
 */
class UsuarioRegistroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('apellidos')
            ->add('email', 'Symfony\Component\Form\Extension\Core\Type\EmailType', array(
                'label' => 'Correo electrónico',
                'attr' => array(
                    'placeholder' => 'usuario@servidor',
                )
            ))

            ->add('password', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', array(
                'type' => 'password',
                'invalid_message' => 'Las dos contraseñas deben coincidir',
                'first_options' => array('label' => 'Contraseña'),
                'second_options' => array('label' => 'Repite Contraseña'),
                'required' => false,
            ))

            ->add('direccion')
            ->add('permite_email', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array('required' => false))
            ->add('fecha_nacimiento', 'Symfony\Component\Form\Extension\Core\Type\BirthdayType', array(
                'years' => range(date('Y') - 18, date('Y') - 18 - 120),
            ))
            ->add('dni')
            ->add('numero_tarjeta', null, array(
                'label' => 'Tarjeta de Crédito',
                'attr' => array(
                    'pattern' => '^[0-9]{13,16}$',
                    'placeholder' => 'Entre 13 y 16 numeros',
                )
            ))

            ->add('ciudad', 'Symfony\Component\Form\Extension\Core\Type\EntityType', array(
                'class' => 'AppBundle\\Entity\\Ciudad',
                'empty_value' => 'Selecciona una ciudad',
                'query_builder' => function (EntityRepository $repositorio) {
                    return $repositorio->createQueryBuilder('c')
                        ->orderBy('c.nombre', 'ASC');
                },
            ))

            ->add('registrarme', 'Symfony\Component\Form\Extension\Core\Type\SubmitType')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Usuario',
            'validation_groups' => array('default', 'registro'),
        ));
    }

    public function getBlockPrefix()
    {
        return 'frontend_usuario';
    }
}
