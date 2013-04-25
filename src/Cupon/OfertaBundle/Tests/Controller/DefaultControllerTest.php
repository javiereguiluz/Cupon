<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\OfertaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test funcional de la portada del sitio y de la acción de comprar una oferta
 * por parte de un usuario anónimo.
 *
 * También asegura el rendimiento de la aplicación obligando a que la portada
 * requiera menos de cuatro consultas a la base de datos y se genere en menos
 * de medio segundo.
 */
class DefaultControllerTest extends WebTestCase
{
    /** @test */
    public function laPortadaSeGeneraCorrectamente()
    {
        $client = static::createClient();

        //SUT
        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode(),
            'La portada se genera correctamente.'
        );
    }

    /** @test */
    public function laPortadaSoloMuestraUnaOfertaActiva()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        //SUT
        $ofertasActivas = $crawler->filter(
            'article.oferta section.descripcion a:contains("Comprar")'
        )->count();

        $this->assertEquals(1, $ofertasActivas,
            'La portada muestra una única oferta activa que se puede comprar'
        );
    }

    /** @test */
    public function losUsuariosPuedenRegistrarseDesdeLaPortada()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        //SUT
        $numeroEnlacesRegistrarse = $crawler->filter('html:contains("Regístrate")')->count();

        $this->assertGreaterThan(0, $numeroEnlacesRegistrarse,
            'La portada muestra al menos un enlace o botón para registrarse'
        );
    }

    /** @test */
    public function losUsuariosAnonimosVenLaCiudadPorDefecto()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        //SUT
        $ciudadPorDefecto = $client->getContainer()->getParameter('cupon.ciudad_por_defecto');
        $ciudadPortada = $crawler->filter('header nav select option[selected="selected"]')->attr('value');

        $this->assertEquals($ciudadPorDefecto, $ciudadPortada,
            'La ciudad seleccionada en la portada de un usuario anónimo es la ciudad por defecto'
        );
    }

    /** @test */
    public function losUsuariosAnonimosNoPuedenComprar()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        //SUT
        $comprar = $crawler->selectLink('Comprar')->link();
        $client->click($comprar);

        $this->assertTrue($client->getResponse()->isRedirect(),
            'Cuando un usuario anónimo intenta comprar, se le redirige al formulario de login'
        );
    }

    /** @test */
    public function losUsuariosAnonimosDebenLoguearseParaPoderComprar()
    {
        $pathLogin = '/.*\/usuario\/login_check/';
        $client = static::createClient();
        $client->followRedirects(true);

        $crawler = $client->request('GET', '/');

        //SUT
        $comprar = $crawler->selectLink('Comprar')->link();
        $crawler = $client->click($comprar);

        $this->assertRegExp($pathLogin, $crawler->filter('article form')->attr('action'),
            'Después de pulsar el botón de comprar, el usuario anónimo ve el formulario de login'
        );
    }

    /** @test */
    public function laPortadaRequierePocasConsultasDeBaseDeDatos()
    {
        $client = static::createClient();
        $client->enableProfiler();

        $client->request('GET', '/');
        //SUT
        if ($profiler = $client->getProfile()) {
            $this->assertLessThan(4, count($profiler->getCollector('db')->getQueries()),
                'La portada requiere menos de 4 consultas a la base de datos'
            );
        }
    }

    /** @test */
    public function laPortadaSeGeneraMuyRapido()
    {
        $client = static::createClient();
        $client->enableProfiler();

        $client->request('GET', '/');

        if ($profiler = $client->getProfile()) {
            // 500 es el tiempo en milisegundos
            $this->assertLessThan(500, $profiler->getCollector('time')->getDuration(),
                'La portada se genera en menos de medio segundo'
            );
        }
    }
}
