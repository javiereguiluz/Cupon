<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Manager;

use AppBundle\Entity\Oferta;
use AppBundle\Entity\Tienda;
use AppBundle\Entity\Usuario;
use AppBundle\Entity\Venta;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class OfertaManager
{
    private $em;

    public function __construct(ObjectManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function comprar(Oferta $oferta, Usuario $usuario)
    {
        $venta = new Venta();

        $venta->setOferta($oferta);
        $venta->setUsuario($usuario);
        $venta->setFecha(new \DateTime());

        $this->em->persist($venta);
        $oferta->setCompras($oferta->getCompras() + 1);

        $this->em->flush();
    }
}
