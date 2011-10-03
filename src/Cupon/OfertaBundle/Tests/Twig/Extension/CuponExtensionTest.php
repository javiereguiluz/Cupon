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

namespace Cupon\OfertaBundle\Tests\Twig\Extension;

use Cupon\OfertaBundle\Twig\Extension\CuponExtension;

/**
 * Test unitario para asegurar el buen funcionamiento de la extensión
 * propia de Twig
 */
class TwigExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testDescuento()
    {
        $extension = new CuponExtension();
        
        $this->assertEquals('-', $extension->descuento(100, null),
            'El descuento no puede ser null'
        );
        $this->assertEquals('-', $extension->descuento('a', 3),
            'El precio debe ser un número'
        );
        $this->assertEquals('-', $extension->descuento(100, 'a'),
            'El descuento debe ser un número'
        );
        
        $this->assertEquals('0%', $extension->descuento(10, 0),
            'Un descuento de cero euros se muestra como 0%'
        );
        $this->assertEquals('-80%', $extension->descuento(2, 8),
            'Si el precio de venta son 2 euros y el descuento sobre el precio original son 8 euros, el descuento es -80%'
        );
        $this->assertEquals('-33%', $extension->descuento(10, 5),
            'Si el precio de venta son 10 euros y el descuento sobre el precio original son 5 euros, el descuento es -33%'
        );
        $this->assertEquals('-33.33%', $extension->descuento(10, 5, 2),
            'Si el precio de venta son 10 euros y el descuento sobre el precio original son 5 euros, el descuento es -33.33% con dos decimales'
        );
    }
    
    public function testMostrarComoLista()
    {
        $extension = new CuponExtension();
        
        $original = file_get_contents(__DIR__.'/fixtures/lista/original.txt');
        $esperado = file_get_contents(__DIR__.'/fixtures/lista/esperado-ul.txt');
        
        $this->assertEquals(
            $esperado,
            $extension->mostrarComoLista($original)
        );
        
        $esperado = file_get_contents(__DIR__.'/fixtures/lista/esperado-ol.txt');
        
        $this->assertEquals(
            $esperado,
            $extension->mostrarComoLista($original, 'ol')
        );
    }
}