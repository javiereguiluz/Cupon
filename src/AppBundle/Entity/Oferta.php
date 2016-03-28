<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Entity;

use AppBundle\Util\Slugger;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OfertaRepository")
 * @Vich\Uploadable
 */
class Oferta
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     */
    protected $nombre;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     */
    protected $slug;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 30)
     */
    protected $descripcion;

    /**
     * @ORM\Column(type="text")
     */
    protected $condiciones;

    /**
     * @ORM\Column(type="string")
     */
    protected $rutaFoto;

    /**
     * @Assert\Image(maxSize = "500k")
     * @Vich\UploadableField(mapping="fotos_ofertas", fileNameProperty="rutaFoto")
     */
    protected $foto;

    /**
     * @ORM\Column(type="decimal", scale=2)
     *
     * @Assert\Range(min = 0)
     */
    protected $precio;

    /**
     * @ORM\Column(type="decimal", scale=2)
     */
    protected $descuento;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     */
    protected $fechaPublicacion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Assert\DateTime
     */
    protected $fechaExpiracion;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\DateTime
     */
    protected $fechaActualizacion;

    /**
     * @ORM\Column(type="integer")
     */
    protected $compras;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\Type(type="integer")
     * @Assert\Range(min = 0)
     */
    protected $umbral;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\Type(type="bool")
     */
    protected $revisada;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Ciudad")
     */
    protected $ciudad;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tienda")
     */
    protected $tienda;

    public function __toString()
    {
        return $this->getNombre();
    }

    public function __construct()
    {
        $this->compras = 0;
        $this->revisada = false;
        $this->fechaActualizacion = new \Datetime();
    }

    /**
     * Este método estático actúa como "constructor con nombre" y simplifica el
     * código de la aplicación ya que rellena los campos de la oferta que no
     * puede rellenar la tienda que ha creado la oferta.
     *
     * @param Tienda $tienda
     *
     * @return Oferta
     */
    public static function crearParaTienda(Tienda $tienda)
    {
        $oferta = new self();

        $oferta->setTienda($tienda);
        $oferta->setCiudad($tienda->getCiudad());

        return $oferta;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
        $this->slug = Slugger::getSlug($nombre);
    }

    /**
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $descripcion
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }

    /**
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * @param string $condiciones
     */
    public function setCondiciones($condiciones)
    {
        $this->condiciones = $condiciones;
    }

    /**
     * @return string
     */
    public function getCondiciones()
    {
        return $this->condiciones;
    }

    /**
     * @param string $rutaFoto
     */
    public function setRutaFoto($rutaFoto)
    {
        $this->rutaFoto = $rutaFoto;
    }

    /**
     * @return string
     */
    public function getRutaFoto()
    {
        return $this->rutaFoto;
    }

    /**
     * @param File $foto
     */
    public function setFoto(File $foto = null)
    {
        $this->foto = $foto;

        // para que el "listener" de Doctrine guarde bien los cambios, al menos
        // una propiedad debe cambiar su valor (además de la propiedad de la foto)
        if (null !== $foto) {
            $this->fechaActualizacion = new \Datetime('now');
        }
    }

    /**
     * @return File
     */
    public function getFoto()
    {
        return $this->foto;
    }

    /**
     * @param float $precio
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;
    }

    /**
     * @return float
     */
    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * @param float $descuento
     */
    public function setDescuento($descuento)
    {
        $this->descuento = $descuento;
    }

    /**
     * @return float
     */
    public function getDescuento()
    {
        return $this->descuento;
    }

    /**
     * @param \DateTime $fechaPublicacion
     */
    public function setFechaPublicacion($fechaPublicacion)
    {
        $this->fechaPublicacion = $fechaPublicacion;
    }

    /**
     * @return \DateTime
     */
    public function getFechaPublicacion()
    {
        return $this->fechaPublicacion;
    }

    /**
     * @param \DateTime $fechaExpiracion
     */
    public function setFechaExpiracion($fechaExpiracion)
    {
        $this->fechaExpiracion = $fechaExpiracion;
    }

    /**
     * @return \DateTime
     */
    public function getFechaExpiracion()
    {
        return $this->fechaExpiracion;
    }

    /**
     * @param \DateTime $fechaActualizacion
     */
    public function setFechaActualizacion($fechaActualizacion)
    {
        $this->fechaActualizacion = $fechaActualizacion;
    }

    /**
     * @return \DateTime
     */
    public function getFechaActualizacion()
    {
        return $this->fechaActualizacion;
    }

    /**
     * @param int $compras
     */
    public function setCompras($compras)
    {
        $this->compras = $compras;
    }

    /**
     * @return int
     */
    public function getCompras()
    {
        return $this->compras;
    }

    /**
     * @param int $umbral
     */
    public function setUmbral($umbral)
    {
        $this->umbral = $umbral;
    }

    /**
     * @return int
     */
    public function getUmbral()
    {
        return $this->umbral;
    }

    /**
     * @param bool $revisada
     */
    public function setRevisada($revisada)
    {
        $this->revisada = $revisada;
    }

    /**
     * @return bool
     */
    public function getRevisada()
    {
        return $this->revisada;
    }

    /**
     * @param Ciudad $ciudad
     */
    public function setCiudad(Ciudad $ciudad)
    {
        $this->ciudad = $ciudad;
    }

    /**
     * @return Ciudad
     */
    public function getCiudad()
    {
        return $this->ciudad;
    }

    /**
     * @param Tienda $tienda
     */
    public function setTienda(Tienda $tienda)
    {
        $this->tienda = $tienda;
    }

    /**
     * @return Tienda
     */
    public function getTienda()
    {
        return $this->tienda;
    }

    /**
     * @Assert\IsTrue(message = "La fecha de expiración debe ser posterior a la fecha de publicación")
     */
    public function isFechaValida()
    {
        if (null === $this->fechaPublicacion || null === $this->fechaExpiracion) {
            return true;
        }

        return $this->fechaExpiracion > $this->fechaPublicacion;
    }
}
