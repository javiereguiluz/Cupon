<?php

// Descomenta el código de esta clase si quieres probar SonataAdminBundle

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

// namespace Cupon\OfertaBundle\Admin;

// use Sonata\AdminBundle\Admin\Admin;
// use Sonata\AdminBundle\Form\FormMapper;
// use Sonata\AdminBundle\Datagrid\DatagridMapper;
// use Sonata\AdminBundle\Datagrid\ListMapper;

// /**
//  * Clase requerida por SonataAdminBundle para gestionar las entidades
//  * de tipo Oferta.
//  */
// class OfertaAdmin extends Admin
// {
//     /**
//      * Define los campos que se muestran en la página que lista las
//      * ofertas disponibles.
//      */
//     protected function configureListFields(ListMapper $mapper)
//     {
//         $mapper
//             ->add('revisada')
//             ->addIdentifier('nombre', null, array('label' => 'Título'))
//             ->add('tienda')
//             ->add('ciudad')
//             ->add('precio')
//             ->add('compras')
//         ;
//     }
// 
//     /**
//      * Define los filtros y campos de búsqueda disponibles para
//      * la sección de administración de ofertas.
//      */
//     protected function configureDatagridFilters(DatagridMapper $mapper)
//     {
//         $mapper
//             ->add('nombre')
//             ->add('descripcion')
//             ->add('ciudad')
//         ;
//     }
// 
//     /**
//      * Define los campos que incluyen los formularios que muestran
//      * y permiten editar los datos de las ofertas.
//      */
//     protected function configureFormFields(FormMapper $mapper)
//     {
//         $mapper
//             ->with('Datos básicos')
//                 ->add('nombre')
//                 ->add('slug', null, array('required' => false))
//                 ->add('descripcion')
//                 ->add('condiciones')
//                 ->add('fecha_publicacion', 'datetime')
//                 ->add('fecha_expiracion', 'datetime')
//                 ->add('revisada')
//             ->end()
//             ->with('Foto')
//                 ->add('foto')
//             ->end()
//             ->with('Precio y compras')
//                 ->add('precio')
//                 ->add('descuento')
//                 ->add('compras')
//                 ->add('umbral')
//             ->end()
//             ->with('Tienda y Ciudad')
//                 ->add('tienda')
//                 ->add('ciudad')
//             ->end()
//         ;
//     }
// }
