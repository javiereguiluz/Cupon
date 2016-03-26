<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

$autoload = __DIR__.'/../../../vendor/autoload.php';
if (!file_exists($autoload)) {
    throw new RuntimeException('Antes de ejecutar los tests de esta aplicación, instala sus dependencias mediante el comando "composer install"');
}
$autoload = require $autoload;

include __DIR__.'/../../../app/AppKernel.php';

$kernel = new AppKernel('test', true);
$kernel->loadClassCache();
$kernel->boot();

$aplicacion = new Application($kernel);
$aplicacion->setAutoExit(false);

// Si existe, borrar el archivo con los datos de prueba de la base de datos
// para crearlos de nuevo antes de ejecutar los tests
$directorioCache = $aplicacion->getKernel()->getContainer()->getParameter('kernel.cache_dir');
if (!file_exists($directorioCache.'/data')) {
    @mkdir($directorioCache.'/data');
}
if (file_exists($directorioCache.'/data/datos.sqlite')) {
    @unlink($directorioCache.'/data/datos.sqlite');
}

// Crear la base de datos
$input = new ArrayInput(array('command' => 'doctrine:database:create'));
$aplicacion->run($input, new NullOutput());

// Crear el esquema de la base de datos
$input = new ArrayInput(array('command' => 'doctrine:schema:create'));
$aplicacion->run($input, new NullOutput());

// Cargar los datos de prueba en la base de datos
$input = new ArrayInput(array('command' => 'doctrine:fixtures:load', '--no-interaction' => true, '--append' => true));
$aplicacion->run($input, new NullOutput());

// Hacer una copia de la base de datos original para utilizar en cada test una
// copia idéntica y sin modificaciones de la base de datos
copy($directorioCache.'/data/datos.sqlite', $directorioCache.'/data/datos_originales.sqlite');

unset($input, $aplicacion);
