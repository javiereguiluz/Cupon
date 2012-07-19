<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\OfertaBundle\Tests;

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
    private $validator;

    protected function setUp()
    {
        $this->validator = ValidatorFactory::buildDefault()->getValidator();
    }

    public function testValidarSlug()
    {
        $oferta = new Oferta();
        //SUT
        $oferta->setNombre('Oferta de prueba');
        $slug = $oferta->getSlug();

        $this->assertEquals('oferta-de-prueba', $slug, 'El slug se asigna automáticamente a partir del nombre');
    }

    public function testValidarDescripcion()
    {
        $oferta = new Oferta();
        $oferta->setNombre('Oferta de prueba');
        //SUT
        list($errores, $error) = $this->validar($oferta);

        $this->assertGreaterThan(0, count($errores), 'La descripción no puede dejarse en blanco');
        $this->assertEquals('This value should not be blank', $error->getMessageTemplate());
        $this->assertEquals('descripcion', $error->getPropertyPath());

        $oferta->setDescripcion('Descripción de prueba');
        list($errores, $error) = $this->validar($oferta);

        $this->assertGreaterThan(0, count($errores), 'La descripción debe tener al menos 30 caracteres');
        $this->assertRegExp("/This value is too short/", $error->getMessageTemplate());
        $this->assertEquals('descripcion', $error->getPropertyPath());
    }

    public function testValidarFechas()
    {
        $oferta = new Oferta();
        $oferta->setNombre('Oferta de prueba');
        $oferta->setDescripcion('Descripción de prueba - Descripción de prueba - Descripción de prueba');
        //SUT
        $oferta->setFechaPublicacion(new \DateTime('today'));
        $oferta->setFechaExpiracion(new \DateTime('yesterday'));
        list($errores, $error) = $this->validar($oferta);

        $this->assertGreaterThan(0, count($errores), 'La fecha de expiración debe ser posterior a la fecha de publicación');
        $this->assertEquals('La fecha de expiración debe ser posterior a la fecha de publicación', $error->getMessageTemplate());
        $this->assertEquals('fechaValida', $error->getPropertyPath());
    }

    public function testValidarUmbral()
    {
        $oferta = new Oferta();
        $oferta->setNombre('Oferta de prueba');
        $oferta->setDescripcion('Descripción de prueba - Descripción de prueba - Descripción de prueba');
        $oferta->setFechaPublicacion(new \DateTime('today'));
        $oferta->setFechaExpiracion(new \DateTime('tomorrow'));
        //SUT
        $oferta->setUmbral(3.5);
        list($errores, $error) = $this->validar($oferta);

        $this->assertGreaterThan(0, count($errores), 'El umbral debe ser un número entero');
        $this->assertRegExp("/This value should be of type/", $error->getMessageTemplate());
        $this->assertEquals('umbral', $error->getPropertyPath());
    }

    public function testValidarPrecio()
    {
        $oferta = new Oferta();
        $oferta->setNombre('Oferta de prueba');
        $oferta->setDescripcion('Descripción de prueba - Descripción de prueba - Descripción de prueba');
        $oferta->setFechaPublicacion(new \DateTime('today'));
        $oferta->setFechaExpiracion(new \DateTime('tomorrow'));
        $oferta->setUmbral(3);
        //SUT
        $oferta->setPrecio(-10);
        list($errores, $error) = $this->validar($oferta);

        $this->assertGreaterThan(0, count($errores), 'El precio no puede ser un número negativo');
        $this->assertRegExp("/This value should be .* or more/", $error->getMessageTemplate());
        $this->assertEquals('precio', $error->getPropertyPath());
    }

    public function testValidarCiudad()
    {
        $oferta = new Oferta();
        $oferta->setNombre('Oferta de prueba');
        $oferta->setDescripcion('Descripción de prueba - Descripción de prueba - Descripción de prueba');
        $oferta->setFechaPublicacion(new \DateTime('today'));
        $oferta->setFechaExpiracion(new \DateTime('tomorrow'));
        $oferta->setUmbral(3);
        $oferta->setPrecio(10.5);
        //SUT
        $oferta->setCiudad($this->getCiudad());
        $slug_ciudad = $oferta->getCiudad()->getSlug();

        $this->assertEquals('ciudad-de-prueba', $slug_ciudad, 'La ciudad se guarda correctamente en la oferta');
    }

    public function testValidarTienda()
    {
        $oferta = new Oferta();
        $oferta->setNombre('Oferta de prueba');
        $oferta->setDescripcion('Descripción de prueba - Descripción de prueba - Descripción de prueba');
        $oferta->setFechaPublicacion(new \DateTime('today'));
        $oferta->setFechaExpiracion(new \DateTime('tomorrow'));
        $oferta->setUmbral(3);
        $oferta->setPrecio(10.5);
        $ciudad = $this->getCiudad();
        $oferta->setCiudad($ciudad);
        //SUT
        $oferta->setTienda($this->getTienda($ciudad));
        $oferta_ciudad = $oferta->getCiudad()->getNombre();
        $oferta_tienda_ciudad = $oferta->getTienda()->getCiudad()->getNombre();

        $this->assertEquals($oferta_ciudad, $oferta_tienda_ciudad, 'La tienda asociada a la oferta es de la misma ciudad en la que se vende la oferta');
    }

    private function validar(Oferta $oferta)
    {
        $errores = $this->validator->validate($oferta);
        $error = $errores[0];

        return array($errores, $error);
    }

    private function getCiudad()
    {
        $ciudad = new Ciudad();
        $ciudad->setNombre('Ciudad de Prueba');

        return $ciudad;
    }

    private function getTienda($ciudad)
    {
        $tienda = new Tienda();
        $tienda->setNombre('Tienda de Prueba');
        $tienda->setCiudad($ciudad);

        return $tienda;
    }

}
