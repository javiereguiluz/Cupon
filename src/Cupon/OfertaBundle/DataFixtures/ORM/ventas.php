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

namespace Cupon\OfertaBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Cupon\OfertaBundle\Entity\Oferta,
    Cupon\UsuarioBundle\Entity\Usuario,
    Cupon\OfertaBundle\Entity\Venta;

/**
 * Fixtures de la entidad Venta.
 * Crea para cada usuario registrado entre 0 y 10 ventas.
 */
class ventas extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 50;
    }
    
    public function load($manager)
    {
        // Obtener todas las ofertas y usuarios de la base de datos
        $ofertas = $manager->getRepository('OfertaBundle:Oferta')->findAll();
        $usuarios = $manager->getRepository('UsuarioBundle:Usuario')->findAll();
        
        foreach ($usuarios as $usuario) {
            $compras = rand(0, 10);
            $comprado = array();
            
            for ($i=0; $i<$compras; $i++) {
                $venta = new Venta();

                $venta->setFecha(new \DateTime('now - '.rand(0, 250).' hours'));
                
                // Sólo se añade una venta:
                //   - si este mismo usuario no ha comprado antes la misma oferta
                //   - si la oferta seleccionada ha sido revisada
                //   - si la fecha de publicación de la oferta es posterior a ahora mismo
                $oferta = $ofertas[rand(0, count($ofertas)-1)];
                while (in_array($oferta->getId(), $comprado)
                       || $oferta->getRevisada() == false
                       || $oferta->getFechaPublicacion() > new \DateTime('now')) {
                    $oferta = $ofertas[rand(0, count($ofertas)-1)];
                }
                $comprado[] = $oferta->getId();
                
                $venta->setOferta($oferta);
                $venta->setUsuario($usuario);
                
                $manager->persist($venta);
                
                $oferta->setCompras($oferta->getCompras() + 1);
                $manager->persist($oferta);
            }
            
            unset($comprado);
        }
        
        $manager->flush();
    }
}