<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\UsuarioBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UsuarioRepository extends EntityRepository
{
    /**
     * Encuentra todas las compras del usuario indicado
     *
     * @param string $usuario El id del usuario
     */
    public function findTodasLasCompras($usuario)
    {
        $em = $this->getEntityManager();

        $consulta = $em->createQuery('
            SELECT v, o, t
            FROM OfertaBundle:Venta v JOIN v.oferta o JOIN o.tienda t
            WHERE v.usuario = :id
            ORDER BY v.fecha DESC
        ');
        $consulta->setParameter('id', $usuario);

        return $consulta->getResult();
    }
}
