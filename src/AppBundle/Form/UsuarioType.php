<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Form;

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
class UsuarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('apellidos', null, array('attr' => array('class' => 'largo')))
            ->add('email', 'Symfony\Component\Form\Extension\Core\Type\EmailType', array(
                'label' => 'Correo electrónico',
                'attr' => array(
                    'class' => 'largo',
                    'placeholder' => 'usuario@servidor',
                ),
            ))
            ->add('passwordEnClaro', 'Symfony\Component\Form\Extension\Core\Type\RepeatedType', array(
                'type' => 'Symfony\Component\Form\Extension\Core\Type\PasswordType',
                'invalid_message' => 'Las dos contraseñas deben coincidir',
                'first_options' => array('label' => 'Contraseña'),
                'second_options' => array('label' => 'Repite Contraseña'),
                'required' => false,
            ))
            ->add('direccion', null, array('label' => 'Dirección postal', 'attr' => array('class' => 'mediana')))
            ->add('permiteEmail', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', array('required' => false))
            ->add('fechaNacimiento', 'Symfony\Component\Form\Extension\Core\Type\BirthdayType', array(
                'label' => 'Fecha de nacimiento',
                'years' => range(date('Y') - 18, date('Y') - 18 - 120),
            ))
            ->add('dni', null, array('label' => 'DNI', 'attr' => array('class' => 'corto')))
            ->add('numeroTarjeta', null, array('label' => 'Número de tarjeta de crédito'))
            ->add('ciudad', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
                'class' => 'AppBundle\\Entity\\Ciudad',
                'placeholder' => 'Selecciona una ciudad',
                'query_builder' => function (EntityRepository $repositorio) {
                    return $repositorio->createQueryBuilder('c')
                        ->orderBy('c.nombre', 'ASC');
                },
            ))
        ;

        if ('crear_usuario' === $options['accion']) {
            $builder->add('registrarme', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array(
                'label' => 'Registrarme',
                'attr' => array('class' => 'boton'),
            ));
        } elseif ('modificar_perfil' === $options['accion']) {
            $builder->add('guardar', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array(
                'label' => 'Guardar cambios',
                'attr' => array('class' => 'boton'),
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'accion' => 'modificar_perfil',
            'data_class' => 'AppBundle\Entity\Usuario',
            'validation_groups' => array('default'),
        ));
    }

    public function getBlockPrefix()
    {
        return 'usuario';
    }
}
