<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTestCase extends WebTestCase
{
    protected function setUp()
    {
        $this->inicializaBaseDeDatos();
    }

    protected function inicializaBaseDeDatos()
    {
        $directorioCache = __DIR__.'/../../../app/cache/test';
        copy($directorioCache.'/data/datos_originales.sqlite', $directorioCache.'/data/datos.sqlite');
    }
}
