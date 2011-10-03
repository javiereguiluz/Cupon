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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Cupon\CiudadBundle\Entity\Ciudad;
use Cupon\OfertaBundle\Entity\Oferta;
use Cupon\TiendaBundle\Entity\Tienda;

/**
 * Fixtures de la entidad Oferta.
 * Crea para cada ciudad 20 ofertas con información muy realista.
 */
class ofertas extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    public function getOrder()
    {
        return 30;
    }
    
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load($manager)
    {
        // Obtener todas las tiendas y ciudades de la base de datos
        $ciudades = $manager->getRepository('CiudadBundle:Ciudad')->findAll();
        $tiendas = $manager->getRepository('TiendaBundle:Tienda')->findAll();
        
        // Ordenar las tiendas por ciudad, para generar ofertas coherentes
        $tiendasPorCiudad = array();
        foreach ($tiendas as $tienda) {
            $tiendasPorCiudad[$tienda->getCiudad()->getId()][] = $tienda;
        }
        
        for ($i=0; $i<count($ciudades); $i++) {
            for ($j=1; $j<=20; $j++) {
                $oferta = new Oferta();
                
                $oferta->setNombre($this->getNombre());
                $oferta->setDescripcion($this->getDescripcion());
                $oferta->setCondiciones($this->getCondiciones());
                $oferta->setFoto('foto'.rand(1,20).'.jpg');
                $oferta->setPrecio(number_format(rand(100, 10000)/100, 2));
                $oferta->setDescuento($oferta->getPrecio() * (rand(10, 70)/100));
                
                // Una oferta se publica hoy, el resto se reparte entre el pasado y el futuro
                if ($j < 10) {
                    $fechaPublicacion = new \DateTime('now - '.($j-1).' days');
                }
                else {
                    $fechaPublicacion = new \DateTime('now + '.($j-9).' days');
                }
                $fechaPublicacion->setTime(23, 59, 59);
                
                // Se debe clonar el valor de la fechaPublicacion porque si se usa directamente
                // el método ->add(), se modificaría el valor original, que no se guarda en la BD
                // hasta que se hace el ->flush()
                $fechaExpiracion = clone $fechaPublicacion;
                $fechaExpiracion->add(\DateInterval::createFromDateString('24 hours'));
                
                $oferta->setFechaPublicacion($fechaPublicacion);
                $oferta->setFechaExpiracion($fechaExpiracion);
                
                $oferta->setCompras(0);
                $oferta->setUmbral(rand(25, 100));
                
                // Se marcan como revisadas aproximadamente el 80% de las ofertas
                $oferta->setRevisada((rand(1, 1000) % 10) < 8);
                
                $oferta->setCiudad($ciudades[$i]);
                
                // Seleccionar aleatoriamente una tienda que pertenezca a la ciudad anterior
                $tiendasDeLaCiudad = $tiendasPorCiudad[$oferta->getCiudad()->getId()];
                $tienda = $tiendasDeLaCiudad[rand(0, count($tiendasDeLaCiudad)-1)];
                $oferta->setTienda($tienda);
                
                $manager->persist($oferta);
                $manager->flush();
                
                // Otorgar el permiso adecuado a cada oferta utilizando la ACL
                
                // Obtener la identidad del objeto oferta y del usuario
                $idObjeto  = ObjectIdentity::fromDomainObject($oferta);
                $idUsuario = UserSecurityIdentity::fromAccount($tienda);
                
                // Buscar si la oferta ya dispone de una ACL previa
                $proveedor = $this->container->get('security.acl.provider');
                
                try {
                    $acl = $proveedor->findAcl($idObjeto, array($idUsuario));
                } catch (\Symfony\Component\Security\Acl\Exception\AclNotFoundException $e) {
                    // El objeto no disponía de ninguna ACL, crearla
                    $acl = $proveedor->createAcl($idObjeto);
                }
                
                // Borrar los ACEs previos que (a lo mejor) dispone este objeto
                $aces = $acl->getObjectAces();
                foreach ($aces as $index => $ace) {
                    $acl->deleteObjectAce($index);
                }
                
                $acl->insertObjectAce($idUsuario, MaskBuilder::MASK_OPERATOR);
                $proveedor->updateAcl($acl);
            }
        }
    }
    
    /**
     * Generador aleatorio de nombres de ofertas
     */
    private function getNombre()
    {
        $nombre = 'Oferta ';
        
        $palabras = array('Lorem', 'Ipsum', 'Sitamet', 'Et', 'At', 'Sed', 'Aut', 'Vel', 'Ut', 'Dum', 'Tincidunt', 'Facilisis', 'Nulla', 'Scelerisque', 'Blandit', 'Ligula', 'Eget', 'Drerit', 'Malesuada', 'Enimsit', 'Libero', 'Penatibus', 'Imperdiet', 'Pendisse', 'Vulputae', 'Natoque', 'Aliquam', 'Dapibus', 'Lacinia');
        
        $numeroPalabras = rand(4, 8);
        
        for ($i=0; $i<$numeroPalabras; $i++) {
            $nombre .= strtolower($palabras[rand(0, count($palabras)-1)]).' ';
        }
        
        return $nombre;
    }
    
    /**
     * Generador aleatorio de descripciones de ofertas
     */
    private function getDescripcion()
    {
        $descripcion = array();
        
        $frases = array(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'Mauris ultricies nunc nec sapien tincidunt facilisis.',
            'Nulla scelerisque blandit ligula eget hendrerit.',
            'Sed malesuada, enim sit amet ultricies semper, elit leo lacinia massa, in tempus nisl ipsum quis libero.',
            'Aliquam molestie neque non augue molestie bibendum.',
            'Pellentesque ultricies erat ac lorem pharetra vulputate.',
            'Donec dapibus blandit odio, in auctor turpis commodo ut.',
            'Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.',
            'Nam rhoncus lorem sed libero hendrerit accumsan.',
            'Maecenas non erat eu justo rutrum condimentum.',
            'Suspendisse leo tortor, tempus in lacinia sit amet, varius eu urna.',
            'Phasellus eu leo tellus, et accumsan libero.',
            'Pellentesque fringilla ipsum nec justo tempus elementum.',
            'Aliquam dapibus metus aliquam ante lacinia blandit.',
            'Donec ornare lacus vitae dolor imperdiet vitae ultricies nibh congue.',
        );
        
        $numeroFrases = rand(4, 7);
        
        for ($i=0; $i<$numeroFrases; $i++) {
            $descripcion[] = $frases[rand(0, count($frases)-1)];
        }
        
        return implode("\n", $descripcion);
    }
    
    /**
     * Generador aleatorio de condiciones de ofertas
     */
    private function getCondiciones()
    {
        $condiciones = '';
        
        $frases = array(
            'Máximo 1 consumición por persona.',
            'No acumulable a otras ofertas.',
            'No disponible para llevar. Debe consumirse en el propio local.',
            'Válido para cualquier día entre semana.',
            'No válido en festivos ni fines de semana.',
            'Reservado el derecho de admisión.',
            'Oferta válida si se realizan consumiciones adicionales por valor de 50 euros.',
            'Válido solamente para comidas, no para cenas.',
        );
        
        $numeroFrases = rand(2, 4);
        
        for ($i=0; $i<$numeroFrases; $i++) {
            $condiciones .= $frases[rand(0, count($frases)-1)].' ';
        }
        
        return $condiciones;
    }
}