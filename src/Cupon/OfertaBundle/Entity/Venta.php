<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\OfertaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Venta
{
    /**
     * @ORM\Column(type="datetime")
     */
    protected $fecha;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Cupon\OfertaBundle\Entity\Oferta")
     */
    protected $oferta;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Cupon\UsuarioBundle\Entity\Usuario")
     */
    protected $usuario;

    /**
     * Set fecha
     *
     * @param datetime $fecha
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }

    /**
     * Get fecha
     *
     * @return datetime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set oferta
     *
     * @param Cupon\OfertaBundle\Entity\Oferta $oferta
     */
    public function setOferta(\Cupon\OfertaBundle\Entity\Oferta $oferta)
    {
        $this->oferta = $oferta;
    }

    /**
     * Get oferta
     *
     * @return Cupon\OfertaBundle\Entity\Oferta
     */
    public function getOferta()
    {
        return $this->oferta;
    }

    /**
     * Set usuario
     *
     * @param Cupon\UsuarioBundle\Entity\Usuario $usuario
     */
    public function setUsuario(\Cupon\UsuarioBundle\Entity\Usuario $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Get usuario
     *
     * @return Cupon\UsuarioBundle\Entity\Usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
}
