<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * This file is part of the Cupon sample application.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\UsuarioBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Cupon\CiudadBundle\Entity\Ciudad;
use Cupon\UsuarioBundle\Entity\Usuario;

/**
 * Fixtures de la entidad Usuario.
 * Crea 500 usuarios de prueba con información muy realista.
 */
class usuarios extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    public function getOrder()
    {
        return 40;
    }
    
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load($manager)
    {
        // Obtener todas las ciudades de la base de datos
        $ciudades = $manager->getRepository('CiudadBundle:Ciudad')->findAll();
        
        for ($i=1; $i<=500; $i++) {
            $usuario = new Usuario();
            
            $usuario->setNombre($this->getNombre());
            $usuario->setApellidos($this->getApellidos());
            $usuario->setEmail('usuario'.$i.'@localhost');
            
            $usuario->setSalt(md5(time()));
            
            $passwordEnClaro = 'usuario'.$i;
            $encoder = $this->container->get('security.encoder_factory')->getEncoder($usuario);
            $passwordCodificado = $encoder->encodePassword($passwordEnClaro, $usuario->getSalt());
            $usuario->setPassword($passwordCodificado);
            
            $usuario->setDireccion('Gran Vía, 1');
            $usuario->setPermiteEmail(true);
            $usuario->setFechaAlta(new \DateTime('now - '.rand(1, 150).' days'));
            $usuario->setFechaNacimiento(new \DateTime('now - '.rand(7000, 20000).' days'));
            
            $dni = substr(rand(), 0, 8);
            $usuario->setDni($dni.substr("TRWAGMYFPDXBNJZSQVHLCKE", strtr($dni, "XYZ", "012")%23, 1));
            
            $usuario->setNumeroTarjeta('1234567890123456');
            $usuario->setCiudad($ciudades[rand(0, count($ciudades)-1)]);
            
            $manager->persist($usuario);
        }
        
        $manager->flush();
    }
    
    /**
     * Generador aleatorio de nombres de personas
     * Aproximadamente genera un 50% de hombres y un 50% de mujeres
     */
    private function getNombre()
    {
        // Los nombres más populares en España según el INE
        // Fuente: http://www.ine.es/daco/daco42/nombyapel/nombyapel.htm
        
        $hombres = array('Antonio', 'José', 'Manuel', 'Francisco', 'Juan', 'David', 'José Antonio', 'José Luis', 'Jesús', 'Javier', 'Francisco Javier', 'Carlos', 'Daniel', 'Miguel', 'Rafael', 'Pedro', 'José Manuel', 'Ángel', 'Alejandro', 'Miguel Ángel', 'José María', 'Fernando', 'Luis', 'Sergio', 'Pablo', 'Jorge', 'Alberto');
        $mujeres = array('María Carmen', 'María', 'Carmen', 'Josefa', 'Isabel', 'Ana María', 'María Dolores', 'María Pilar', 'María Teresa', 'Ana', 'Francisca', 'Laura', 'Antonia', 'Dolores', 'María Angeles', 'Cristina', 'Marta', 'María José', 'María Isabel', 'Pilar', 'María Luisa', 'Concepción', 'Lucía', 'Mercedes', 'Manuela', 'Elena', 'Rosa María');
        
        if (rand() % 2) {
            return $hombres[rand(0, count($hombres)-1)];
        }
        else {
            return $mujeres[rand(0, count($mujeres)-1)];
        }
    }
    
    /**
     * Generador aleatorio de apellidos de personas
     */
    private function getApellidos()
    {
        // Los apellidos más populares en España según el INE
        // Fuente: http://www.ine.es/daco/daco42/nombyapel/nombyapel.htm
        
        $apellidos = array('García', 'González', 'Rodríguez', 'Fernández', 'López', 'Martínez', 'Sánchez', 'Pérez', 'Gómez', 'Martín', 'Jiménez', 'Ruiz', 'Hernández', 'Díaz', 'Moreno', 'Álvarez', 'Muñoz', 'Romero', 'Alonso', 'Gutiérrez', 'Navarro', 'Torres', 'Domínguez', 'Vázquez', 'Ramos', 'Gil', 'Ramírez', 'Serrano', 'Blanco', 'Suárez', 'Molina', 'Morales', 'Ortega', 'Delgado', 'Castro', 'Ortíz', 'Rubio', 'Marín', 'Sanz', 'Iglesias', 'Nuñez', 'Medina', 'Garrido');
        
        return $apellidos[rand(0, count($apellidos)-1)].' '.$apellidos[rand(0, count($apellidos)-1)];
    }
}