<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Manager;

use AppBundle\Entity\Tienda;
use AppBundle\Util\Slugger;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class TiendaManager
{
    private $em;
    private $encoderFactory;

    public function __construct(ObjectManager $entityManager, EncoderFactoryInterface $encoderFactory)
    {
        $this->em = $entityManager;
        $this->encoderFactory = $encoderFactory;
    }

    public function guardar(Tienda $tienda)
    {
        if (null !== $tienda->getPasswordEnClaro()) {
            $this->codificarPassword($tienda);
        }

        $this->em->persist($tienda);
        $this->em->flush();
    }

    private function codificarPassword(Tienda $tienda)
    {
        $encoder = $this->encoderFactory->getEncoder($tienda);
        $passwordCodificado = $encoder->encodePassword($tienda->getPasswordEnClaro(), $tienda->getSalt());
        $tienda->setPassword($passwordCodificado);
    }
}
