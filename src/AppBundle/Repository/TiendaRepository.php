<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TiendaRepository extends EntityRepository
{
    /**
     * Encuentra las ofertas más recientes de la tienda indicada.
     *
     * @param int $tiendaId El id de la tienda
     * @param int $limite   Número de ofertas a devolver (por defecto, cinco)
     *
     * @return array
     */
    public function findOfertasRecientes($tiendaId, $limite = 5)
    {
        $em = $this->getEntityManager();

        $consulta = $em->createQuery('
            SELECT o, t
            FROM AppBundle:Oferta o JOIN o.tienda t
            WHERE o.tienda = :id
            ORDER BY o.fechaExpiracion DESC
        ');
        $consulta->setMaxResults($limite);
        $consulta->setParameter('id', $tiendaId);
        $consulta->useResultCache(true, 3600);

        return $consulta->getResult();
    }

    /**
     * Encuentra las ofertas más recientemente publicadas por la tienda indicada
     * Las ofertas devueltas, además de publicadas, también han sido revisadas.
     *
     * @param int $tiendaId
     * @param int $limite   Número de ofertas a devolver (por defecto, diez)
     *
     * @return array
     */
    public function findUltimasOfertasPublicadas($tiendaId, $limite = 10)
    {
        $em = $this->getEntityManager();

        $consulta = $em->createQuery('
            SELECT o, t
            FROM AppBundle:Oferta o JOIN o.tienda t
            WHERE o.revisada = true AND o.fechaPublicacion < :fecha AND o.tienda = :id
            ORDER BY o.fechaExpiracion DESC
        ');
        $consulta->setMaxResults($limite);
        $consulta->setParameter('id', $tiendaId);
        $consulta->setParameter('fecha', new \DateTime('now'));

        return $consulta->getResult();
    }

    /**
     * Encuentra las tiendas más cercanas a la tienda indicada.
     *
     * @param string $tienda El slug de la tienda para la que se buscan tiendas cercanas
     * @param string $ciudad El slug de la ciudad a la que pertenece la tienda
     *
     * @return array
     */
    public function findCercanas($tienda, $ciudad)
    {
        $em = $this->getEntityManager();

        $consulta = $em->createQuery('
            SELECT t, c
            FROM AppBundle:Tienda t JOIN t.ciudad c
            WHERE c.slug = :ciudad AND t.slug != :tienda
        ');
        $consulta->setMaxResults(5);
        $consulta->setParameter('ciudad', $ciudad);
        $consulta->setParameter('tienda', $tienda);
        $consulta->useResultCache(true, 600);

        return $consulta->getResult();
    }
}
