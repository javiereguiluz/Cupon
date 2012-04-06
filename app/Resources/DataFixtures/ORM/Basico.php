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

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Cupon\CiudadBundle\Entity\Ciudad;
use Cupon\OfertaBundle\Entity\Oferta;
use Cupon\TiendaBundle\Entity\Tienda;
use Cupon\UsuarioBundle\Entity\Usuario;
use Cupon\OfertaBundle\Entity\Venta;

/**
 * Versión simplificada de los fixtures completos de la aplicación.
 * Se ha eliminado todo el código que hace uso de la ACL y del componente
 * de seguridad.
 *
 * Este es el archivo que debes utilizar si estás creando la aplicación a mano
 * y todavía no has llegado al capítulo de la seguridad. Carga estos fixtures
 * básicos ejecutando el siguiente comando:
 *
 * $ php app/console doctrine:fixtures:load --fixtures=app/Resources
 * 
 * Al utilizar este archivo de datos simplificado, la configuración de seguridad
 * de la aplicación debe indicar que los usuarios de tipo `Usuario` guardan la
 * contraseña en claro, sin codificar.
 * 
 * Asegúrate de que en el archivo `security.yml` tengas la siguiente configuración:
 *   security:
 *     # ...
 *     encoders:
 *       Cupon\UsuarioBundle\Entity\Usuario: plaintext
 */
class Basico implements FixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $manager)
    {
        // Crear 10 ciudades de prueba
        for ($i=1; $i<=10; $i++) {
            $ciudad = new Ciudad();
            $ciudad->setNombre('Ciudad #'.$i);
            
            $manager->persist($ciudad);
        }
        $manager->flush();
        
        // Crear 10 tiendas en cada ciudad
        $ciudades = $manager->getRepository('CiudadBundle:Ciudad')->findAll();
        $numTienda = 0;
        foreach ($ciudades as $ciudad) {
            for ($i=1; $i<=10; $i++) {
                $numTienda++;
                
                $tienda = new Tienda();
                $tienda->setNombre('Tienda #'.$numTienda);
                $tienda->setLogin('tienda'.$numTienda);
                $tienda->setPassword('password'.$numTienda);
                $tienda->setSalt(md5(time()));
                $tienda->setDescripcion('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat');
                $tienda->setDireccion("Calle Lorem Ipsum, 5\n".$ciudad->getNombre());
                $tienda->setCiudad($ciudad);
                
                $manager->persist($tienda);
            }
        }
        $manager->flush();
        
        // Crear 50 ofertas en cada ciudad
        $ciudades = $manager->getRepository('CiudadBundle:Ciudad')->findAll();
        $numOferta = 0;
        foreach ($ciudades as $ciudad) {
            $tiendas = $manager->getRepository('TiendaBundle:Tienda')->findByCiudad(
                $ciudad->getId()
            );

            for ($i=1; $i<=50; $i++) {
                $numOferta++;
                
                $oferta = new Oferta();
                
                $oferta->setNombre('Oferta #'.$numOferta.' lorem ipsum dolor sit amet');
                $oferta->setDescripcion("Lorem ipsum dolor sit amet, consectetur adipisicing.\nElit, sed do eiusmod tempor incididunt.\nUt labore et dolore magna aliqua.\nNostrud exercitation ullamco laboris nisi ut");
                $oferta->setCondiciones("Labore et dolore magna aliqua. Ut enim ad minim veniam.");
                $oferta->setFoto('foto'.rand(1,20).'.jpg');
                $oferta->setPrecio(number_format(rand(100, 10000)/100, 2));
                $oferta->setDescuento($oferta->getPrecio() * (rand(10, 70)/100));
                
                // Se publican 9 ofertas en el pasado, 1 en el presente y 40 en el futuro
                if ($i < 11) {
                    $fechaPublicacion = new \DateTime('now - '.($i-1).' days');
                }
                else {
                    $fechaPublicacion = new \DateTime('now + '.($i-10).' days');
                }
                $fechaPublicacion->setTime(23, 59, 59);
                
                $fechaExpiracion = clone $fechaPublicacion;
                $fechaExpiracion->add(\DateInterval::createFromDateString('24 hours'));
                
                $oferta->setFechaPublicacion($fechaPublicacion);
                $oferta->setFechaExpiracion($fechaExpiracion);
                
                $oferta->setCompras(0);
                $oferta->setUmbral(rand(25, 100));
                
                // Se marcan como revisadas aproximadamente el 90% de las ofertas
                $oferta->setRevisada((rand(1, 1000) % 10) < 9);
                
                $oferta->setCiudad($ciudad);
                
                // Seleccionar aleatoriamente una tienda que pertenezca a la ciudad
                $oferta->setTienda($tiendas[array_rand($tiendas)]);
                
                $manager->persist($oferta);
            }
        }
        $manager->flush();
        
        // Crear 100 usuarios en cada ciudad
        $numUsuario = 0;
        foreach ($ciudades as $ciudad) {
            for ($i=1; $i<=100; $i++) {
                $numUsuario++;
                
                $usuario = new Usuario();
                
                $usuario->setNombre('Usuario #'.$numUsuario);
                $usuario->setApellidos('Apellido1 Apellido2');
                $usuario->setEmail('usuario'.$numUsuario.'@localhost');
                $usuario->setSalt('');
                $usuario->setPassword('password'.$numUsuario);
                $usuario->setDireccion("Calle Ipsum Lorem, 2\n".$ciudad->getNombre());
                // El 60% de los usuarios permite email
                $usuario->setPermiteEmail((rand(1, 1000) % 10) < 6);
                $usuario->setFechaAlta(new \DateTime('now - '.rand(1, 150).' days'));
                $usuario->setFechaNacimiento(new \DateTime('now - '.rand(7000, 20000).' days'));
                
                $dni = substr(rand(), 0, 8);
                $usuario->setDni($dni.substr(
                    "TRWAGMYFPDXBNJZSQVHLCKE",
                    strtr($dni, "XYZ", "012")%23, 1)
                );
                
                $usuario->setNumeroTarjeta('1234567890123456');
                $usuario->setCiudad($ciudad);
                
                $manager->persist($usuario);
            }
        }
        $manager->flush();
        
        // Crear 500 ventas aleatorias
        $ofertas  = $manager->getRepository('OfertaBundle:Oferta')->findAll();
        $usuarios = $manager->getRepository('UsuarioBundle:Usuario')->findAll();
        
        foreach ($usuarios as $usuario) {
            $compras = rand(0, 10);
            $comprado = array();
            
            for ($i = 0; $i < $compras; $i++) {
                $venta = new Venta();
                
                // Sólo se añade una venta:
                //   - si este mismo usuario no ha comprado antes la misma oferta
                //   - si la oferta seleccionada ha sido revisada
                //   - si la fecha de publicación de la oferta es posterior a ahora mismo
                $oferta = $ofertas[array_rand($ofertas)];
                while (in_array($oferta->getId(), $comprado)
                       || $oferta->getRevisada() == false
                       || $oferta->getFechaPublicacion() > new \DateTime('now')) {
                    $oferta = $ofertas[array_rand($ofertas)];
                }
                $comprado[] = $oferta->getId();
                
                $venta->setOferta($oferta);
                $venta->setUsuario($usuario);
                
                $publicacion = clone $oferta->getFechaPublicacion();
                $venta->setFecha(
                    $publicacion->add(\DateInterval::createFromDateString(rand(10, 10000).' seconds'))
                );
                
                $manager->persist($venta);
                
                $oferta->setCompras($oferta->getCompras() + 1);
                $manager->persist($oferta);
            }
            
            unset($comprado);
        }
        $manager->flush();
    }
}