<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\CiudadBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CiudadRepository extends EntityRepository
{
    /**
     * Devuelve un array simple con todas las ciudades disponibles
     */
    public function findListaCiudades()
    {
        $em = $this->getEntityManager();

        $consulta = $em->createQuery('
            SELECT c
            FROM CiudadBundle:Ciudad c
            ORDER BY c.nombre
        ');
        $consulta->useResultCache(true, 3600);

        return $consulta->getArrayResult();
    }

    /**
     * Encuentra las cinco ciudades más cercanas a la ciudad indicada
     *
     * @param string $ciudad_id El id de la ciudad para la que se buscan cercanas
     */
    public function findCercanas($ciudad_id)
    {
        $em = $this->getEntityManager();

        $consulta = $em->createQuery('
            SELECT c
            FROM CiudadBundle:Ciudad c
            WHERE c.id != :id
            ORDER BY c.nombre ASC
        ');
        $consulta->setMaxResults(5);
        $consulta->setParameter('id', $ciudad_id);
        $consulta->useResultCache(true, 3600);

        return $consulta->getResult();
    }

    /**
     * Encuentra todas las ofertas de la ciudad indicada
     *
     * @param string $ciudad El slug de la ciudad para la que se buscan sus ofertas
     */
    public function findTodasLasOfertas($ciudad)
    {
        return $this->queryTodasLasOfertas($ciudad)->getResult();
    }

    /**
     * Método especial asociado con `findTodasLasOfertas()` y que devuelve solamente
     * la consulta necesaria para obtener todas las ofertas de la ciudad indicada.
     * Se utiliza para la paginación de resultados.
     *
     * @param string $ciudad El slug de la ciudad
     */
    public function queryTodasLasOfertas($ciudad)
    {
        $em = $this->getEntityManager();

        $consulta = $em->createQuery('
            SELECT o, t
            FROM OfertaBundle:Oferta o JOIN o.tienda t JOIN o.ciudad c
            WHERE c.slug = :ciudad
            ORDER BY o.fecha_publicacion DESC
        ');
        $consulta->setParameter('ciudad', $ciudad);
        $consulta->useResultCache(true, 600);

        return $consulta;
    }

    /**
     * Encuentra todos los usuarios asociados a la ciudad indicada
     *
     * @param string $ciudad El slug de la ciudad para la que se buscan sus usuarios
     */
    public function findTodosLosUsuarios($ciudad)
    {
        return $this->queryTodosLosUsuarios($ciudad)->getResult();
    }

    /**
     * Método especial asociado con `findTodosLosUsuarios()` y que devuelve solamente
     * la consulta necesaria para obtener todos los usuarios de la ciudad indicada.
     * Se utiliza para la paginación de resultados.
     *
     * @param string $ciudad El slug de la ciudad
     */
    public function queryTodosLosUsuarios($ciudad)
    {
        $em = $this->getEntityManager();

        $consulta = $em->createQuery('
            SELECT u
            FROM UsuarioBundle:Usuario u JOIN u.ciudad c
            WHERE c.slug = :ciudad
            ORDER BY u.apellidos ASC
        ');
        $consulta->setParameter('ciudad', $ciudad);

        return $consulta;
    }

    /**
     * Encuentra todas las tiendas asociadas a la ciudad indicada
     *
     * @param string $ciudad El slug de la ciudad para la que se buscan sus tiendas
     */
    public function findTodasLasTiendas($ciudad)
    {
        return $this->queryTodasLasTiendas($ciudad)->getResult();
    }

    /**
     * Método especial asociado con `findTodasLasTiendas()` y que devuelve solamente
     * la consulta necesaria para obtener todas las tiendas de la ciudad indicada.
     * Se utiliza para la paginación de resultados.
     *
     * @param string $ciudad El slug de la ciudad
     */
    public function queryTodasLasTiendas($ciudad)
    {
        $em = $this->getEntityManager();

        $consulta = $em->createQuery('
            SELECT t
            FROM TiendaBundle:Tienda t JOIN t.ciudad c
            WHERE c.slug = :ciudad
            ORDER BY t.nombre ASC
        ');
        $consulta->setParameter('ciudad', $ciudad);
        $consulta->useResultCache(true, 600);

        return $consulta;
    }
}
