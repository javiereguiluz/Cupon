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

namespace Cupon\OfertaBundle\Tests\Entity;

use Symfony\Component\Validator\ValidatorFactory;
use Cupon\OfertaBundle\Entity\Oferta;
use Cupon\CiudadBundle\Entity\Ciudad;
use Cupon\TiendaBundle\Entity\Tienda;

/**
 * Test unitario para asegurar que la validación de la entidad Oferta
 * funciona correctamente
 */
class OfertaTest extends \PHPUnit_Framework_TestCase
{
    protected $tienda;
    protected $ciudad;

    protected function setUp()
    {
        $ciudad = new Ciudad();
        $ciudad->setNombre('Ciudad de Prueba');
        $this->ciudad = $ciudad;
        
        $tienda = new Tienda();
        $tienda->setNombre('Tienda de Prueba');
        $tienda->setCiudad($this->ciudad);
        $this->tienda = $tienda;
    }
    
    public function testValidacion()
    {
        $validator = ValidatorFactory::buildDefault()->getValidator();
        
        $oferta = new Oferta();
        $oferta->setNombre('Oferta de prueba');
        
        $this->assertEquals('oferta-de-prueba', $oferta->getSlug(),
            'El slug se asigna automáticamente a partir del nombre'
        );
        
        $errores = $validator->validate($oferta);
        $this->assertGreaterThan(0, count($errores),
            'La descripción no puede dejarse en blanco'
        );
        
        $error = $errores[0];
        $this->assertEquals('This value should not be blank', $error->getMessageTemplate());
        $this->assertEquals('descripcion', $error->getPropertyPath());
        
        $oferta->setDescripcion('Descripción de prueba');
        
        $errores = $validator->validate($oferta);
        $this->assertGreaterThan(0, count($errores),
            'La descripción debe tener al menos 30 caracteres'
        );
        
        $error = $errores[0];
        $this->assertRegExp("/This value is too short/", $error->getMessageTemplate());
        $this->assertEquals('descripcion', $error->getPropertyPath());
        
        $oferta->setDescripcion('Descripción de prueba - Descripción de prueba - Descripción de prueba');
        $oferta->setFechaPublicacion(new \DateTime('today'));
        $oferta->setFechaExpiracion(new \DateTime('yesterday'));
        
        $errores = $validator->validate($oferta);
        $this->assertGreaterThan(0, count($errores),
            'La fecha de expiración debe ser posterior a la fecha de publicación'
        );
        
        $error = $errores[0];
        $this->assertEquals('La fecha de expiración debe ser posterior a la fecha de publicación', $error->getMessageTemplate());
        $this->assertEquals('fechaValida', $error->getPropertyPath());
        
        $oferta->setFechaExpiracion(new \DateTime('tomorrow'));
        $oferta->setUmbral(3.5);
        
        $errores = $validator->validate($oferta);
        $this->assertGreaterThan(0, count($errores),
            'El umbral debe ser un número entero'
        );
        
        $error = $errores[0];
        $this->assertRegExp("/This value should be of type/", $error->getMessageTemplate());
        $this->assertEquals('umbral', $error->getPropertyPath());
        
        $oferta->setUmbral(3);
        $oferta->setPrecio(-10);
        
        $errores = $validator->validate($oferta);
        $this->assertGreaterThan(0, count($errores),
            'El precio no puede ser un número negativo'
        );
        
        $error = $errores[0];
        $this->assertRegExp("/This value should be .* or more/", $error->getMessageTemplate());
        $this->assertEquals('precio', $error->getPropertyPath());
        
        $oferta->setPrecio(10.5);
        $oferta->setCiudad($this->ciudad);
        
        $this->assertEquals('ciudad-de-prueba', $oferta->getCiudad()->getSlug(),
            'La ciudad se guarda correctamente en la oferta'
        );
        
        $oferta->setTienda($this->tienda);
        $this->assertEquals(
            $oferta->getCiudad()->getNombre(),
            $oferta->getTienda()->getCiudad()->getNombre(),
            'La tienda asociada a la oferta es de la misma ciudad en la que se vende la oferta'
        );
    }
}