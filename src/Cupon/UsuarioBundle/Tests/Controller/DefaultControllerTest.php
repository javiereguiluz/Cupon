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

namespace Cupon\UsuarioBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test funcional para comprobar que funciona bien el registro de usuarios
 * en el frontend
 */
class DefaultControllerTest extends WebTestCase
{
    public function provider()
    {
        return array(
            array(
                array(
                    'frontend_usuario[nombre]'                  => 'Anónimo',
                    'frontend_usuario[apellidos]'               => 'Apellido1 Apellido2',
                    'frontend_usuario[email]'                   => 'anonimo'.uniqid().'@localhost.localdomain',
                    'frontend_usuario[password][first]'         => 'anonimo1234',
                    'frontend_usuario[password][second]'        => 'anonimo1234',
                    'frontend_usuario[direccion]'               => 'Mi calle, Mi ciudad, 01001',
                    'frontend_usuario[fecha_nacimiento][day]'   => '01',
                    'frontend_usuario[fecha_nacimiento][month]' => '01',
                    'frontend_usuario[fecha_nacimiento][year]'  => '1970',
                    'frontend_usuario[dni]'                     => '11111111H',
                    'frontend_usuario[numero_tarjeta]'          => '123456789012345',
                    'frontend_usuario[ciudad]'                  => '1',
                    'frontend_usuario[permite_email]'           => '1'
                )
            )
        );
    }
    
    /**
     * @dataProvider provider
     */
    public function testRegistro($usuario)
    {
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/');
        $crawler = $client->followRedirect();
        
        $registrate = $crawler->selectLink('Regístrate ya')->link();
        $crawler = $client->click($registrate);
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Regístrate gratis como usuario")')->count(),
            'Después de pulsar el botón Regístrate de la portada, se carga la página con el formulario de registro'
        );
        
        $formulario = $crawler->selectButton('Registrarme')->form($usuario);
        
        $client->submit($formulario);
        $crawler = $client->followRedirect();
        
        $crawler = $client->followRedirect();
        
        $perfil = $crawler->filter('aside section#login')->selectLink('Ver mi perfil')->link();
        $crawler = $client->click($perfil);
        
        $this->assertEquals(
            $usuario['frontend_usuario[email]'],
            $crawler->filter('form input[name="frontend_usuario[email]"]')->attr('value'),
            'El usuario se ha registrado correctamente y sus datos se han guardado en la base de datos'
        );
    }
}
