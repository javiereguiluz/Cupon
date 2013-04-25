<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\UsuarioBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test funcional para comprobar que funciona bien el registro de usuarios
 * en el frontend, además del perfil y el proceso de baja del usuario
 */
class DefaultControllerTest extends WebTestCase
{
    private $em;

    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @dataProvider usuarios
     */
    public function testRegistroPerfilBaja($usuario)
    {
        $client = static::createClient();
        $client->followRedirects(true);

        $crawler = $client->request('GET', '/');

        // Registrarse como nuevo usuario
        $enlaceRegistro = $crawler->selectLink('Regístrate ya')->link();
        $crawler = $client->click($enlaceRegistro);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Regístrate gratis como usuario")')->count(),
            'Después de pulsar el botón Regístrate de la portada, se carga la página con el formulario de registro'
        );

        // Cuando se cargan los archivos de fixtures, el atributo 'id' asignado
        // a cada ciudad es un valor que se autoincrementa. Si la base de datos
        // resetea este contador cada vez, los 'id' de las ciudades seran 1, 2, 3, ...
        // Si no se resetean, no es posible saber cuál será el 'id' valido de alguna ciudad
        // Por ello, se utiliza el siguiente truco:
        //   1. Se obtiene el campo de formulario que permite elegir la ciudad
        //   2. Se extraen todos los valores de la lista <select>
        //   3. Se escoge la ciudad en la posición [1] del array de ciudades, ya que la
        //      posición [0] suele estar vacía o muestra un mensaje al usuario
        $listaDesplegable = $crawler
            ->selectButton('Registrarme')
            ->form()
            ->get("frontend_usuario[ciudad]")
        ;
        $atributosIdCiudades = $listaDesplegable->availableOptionValues();
        $idValidoCiudad = $atributosIdCiudades[1];
        $usuario['frontend_usuario[ciudad]'] = $idValidoCiudad;

        $formulario = $crawler->selectButton('Registrarme')->form($usuario);
        $crawler = $client->submit($formulario);

        $this->assertTrue($client->getResponse()->isSuccessful());

        // Comprobar que el cliente ahora dispone de una cookie de sesión
        $this->assertRegExp('/(\d|[a-z])+/', $client->getCookieJar()->get('MOCKSESSID', '/', 'localhost')->getValue(),
            'La aplicación ha enviado una cookie de sesión'
        );

        // Acceder al perfil del usuario recién creado
        $perfil = $crawler->filter('aside section#login')->selectLink('Ver mi perfil')->link();
        $crawler = $client->click($perfil);

        $this->assertEquals(
            $usuario['frontend_usuario[email]'],
            $crawler->filter('form input[name="frontend_usuario[email]"]')->attr('value'),
            'El usuario se ha registrado correctamente y sus datos se han guardado en la base de datos'
        );

        // Dar de baja al usuario aleatorio recién creado
        $usuario = $this->em->getRepository('UsuarioBundle:Usuario')->findOneByEmail($usuario['frontend_usuario[email]']);
        $this->em->remove($usuario);
        $this->em->flush();
    }

    /**
     * Método que provee de usuarios de prueba a los tests de esta clase
     */
    public function usuarios()
    {
        return array(
            array(
                array(
                    'frontend_usuario[nombre]'           => 'Anónimo',
                    'frontend_usuario[apellidos]'        => 'Apellido1 Apellido2',
                    'frontend_usuario[email]'            => 'anonimo'.uniqid().'@localhost.localdomain',
                    'frontend_usuario[password][first]'  => 'anonimo1234',
                    'frontend_usuario[password][second]' => 'anonimo1234',
                    'frontend_usuario[direccion]'        => 'Mi calle, Mi ciudad, 01001',
                    'frontend_usuario[dni]'              => '11111111H',
                    'frontend_usuario[numero_tarjeta]'   => '123456789012345',
                    'frontend_usuario[ciudad]'           => '1',
                    'frontend_usuario[permite_email]'    => '1'
                )
            )
        );
    }
}
