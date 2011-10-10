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
class ValidacionOfertaTest extends \PHPUnit_Framework_TestCase {

    private $validator;

    protected function setUp() {
        $this->validator = ValidatorFactory::buildDefault()->getValidator();
    }

    public function test_validar_slug() {
        $oferta = new Oferta();
        //SUT
        $oferta->setNombre('Oferta de prueba');
        $slug = $oferta->getSlug();

        $this->assertEquals('oferta-de-prueba', $slug, 'El slug se asigna automáticamente a partir del nombre');
    }

    public function test_descripcion_no_puede_estar_vacia() {
        $oferta = new Oferta();
        $oferta->setNombre('Oferta de prueba');
        //SUT
        list($errores, $error) = $this->validar($oferta);

        $this->assertGreaterThan(0, count($errores), 'La descripción no puede dejarse en blanco');
        $this->assertEquals('This value should not be blank', $error->getMessageTemplate());
        $this->assertEquals('descripcion', $error->getPropertyPath());
    }
    

    public function test_descripcion_debe_tener_un_minimo_de_treinta_caracteres() {
        $oferta = new Oferta();
        $oferta->setNombre('Oferta de prueba');
        //SUT
        $oferta->setDescripcion('Descripción de prueba');
        list($errores, $error) = $this->validar($oferta);

        $this->assertGreaterThan(0, count($errores), 'La descripción debe tener al menos 30 caracteres');
        $this->assertRegExp("/This value is too short/", $error->getMessageTemplate());
        $this->assertEquals('descripcion', $error->getPropertyPath());
    }

    public function test_la_fecha_de_expiracion_debe_ser_posterior_a_la_de_publicacion() {
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

    public function test_el_umbral_debe_ser_un_numero_entero() {
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
    
    public function test_el_precio_no_puede_ser_numero_negativo(){
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
    
    public function test_la_ciudad_se_guarda_correctamente(){
        $oferta = new Oferta();
        $oferta->setNombre('Oferta de prueba');
        $oferta->setDescripcion('Descripción de prueba - Descripción de prueba - Descripción de prueba');
        $oferta->setFechaPublicacion(new \DateTime('today'));
        $oferta->setFechaExpiracion(new \DateTime('tomorrow'));
        $oferta->setUmbral(3);
        $oferta->setPrecio(10.5);
        //SUT
        $oferta->setCiudad($this->obtenerCiudad());
        $slug_ciudad = $oferta->getCiudad()->getSlug();

        $this->assertEquals('ciudad-de-prueba', $slug_ciudad, 'La ciudad se guarda correctamente en la oferta');
    }
    
    public function test_la_tienda_asociada_a_la_oferta_es_de_la_misma_ciudad(){
        $oferta = new Oferta();
        $oferta->setNombre('Oferta de prueba');
        $oferta->setDescripcion('Descripción de prueba - Descripción de prueba - Descripción de prueba');
        $oferta->setFechaPublicacion(new \DateTime('today'));
        $oferta->setFechaExpiracion(new \DateTime('tomorrow'));
        $oferta->setUmbral(3);
        $oferta->setPrecio(10.5);
        $ciudad = $this->obtenerCiudad();
        $oferta->setCiudad($ciudad);
        //SUT
        $oferta->setTienda($this->obtenerTienda($ciudad));
        $oferta_ciudad = $oferta->getCiudad()->getNombre();
        $oferta_tienda_ciudad = $oferta->getTienda()->getCiudad()->getNombre();
        
        $this->assertEquals($oferta_ciudad, $oferta_tienda_ciudad, 'La tienda asociada a la oferta es de la misma ciudad en la que se vende la oferta');        
    }

    private function validar(Oferta $oferta){
        $errores = $this->validator->validate($oferta);
        $error = $errores[0];
        
        return array($errores, $error);
    }
    
    
    private function obtenerCiudad() {
        $ciudad = new Ciudad();
        $ciudad->setNombre('Ciudad de Prueba');
        
        return $ciudad;
    }

    private function obtenerTienda($ciudad) {
        $tienda = new Tienda();
        $tienda->setNombre('Tienda de Prueba');
        $tienda->setCiudad($ciudad);

        return $tienda;
    }

}