<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Venta
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $fecha;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Oferta")
     */
    protected $oferta;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario")
     */
    protected $usuario;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $fecha
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }

    /**
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @param Oferta $oferta
     */
    public function setOferta(Oferta $oferta)
    {
        $this->oferta = $oferta;
    }

    /**
     * @return Oferta
     */
    public function getOferta()
    {
        return $this->oferta;
    }

    /**
     * @param Usuario $usuario
     */
    public function setUsuario(Usuario $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * @return Usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
}
