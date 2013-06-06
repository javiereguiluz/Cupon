<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\UsuarioBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * Cupon\UsuarioBundle\Entity\Usuario
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Cupon\UsuarioBundle\Entity\UsuarioRepository")
 * @DoctrineAssert\UniqueEntity("email")
 * @Assert\Callback(methods={"esDniValido"})
 */
class Usuario implements UserInterface
{
    /**
     * Método requerido por la interfaz UserInterface
     */
    public function eraseCredentials()
    {
    }

    /**
     * Método requerido por la interfaz UserInterface
     */
    public function getRoles()
    {
        return array('ROLE_USUARIO');
    }

    /**
     * Método requerido por la interfaz UserInterface
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $nombre
     *
     * @ORM\Column(name="nombre", type="string", length=100)
     * @Assert\NotBlank()
     */
    private $nombre;

    /**
     * @var string $apellidos
     *
     * @ORM\Column(name="apellidos", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $apellidos;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=255)
     * @Assert\NotBlank(groups={"registro"})
     * @Assert\Length(min = 6)
     */
    private $password;

    /**
     * @var string salt
     *
     * @ORM\Column(name="salt", type="string", length=255)
     */
    protected $salt;

    /**
     * @var text $direccion
     *
     * @ORM\Column(name="direccion", type="text")
     * @Assert\NotBlank()
     */
    private $direccion;

    /**
     * @var boolean $permite_email
     *
     * @ORM\Column(name="permite_email", type="boolean")
     * @Assert\Type(type="bool")
     */
    private $permite_email;

    /**
     * @var datetime $fecha_alta
     *
     * @ORM\Column(name="fecha_alta", type="datetime")
     * @Assert\DateTime()
     */
    private $fecha_alta;

    /**
     * @var datetime $fecha_nacimiento
     *
     * @ORM\Column(name="fecha_nacimiento", type="datetime")
     * @Assert\DateTime()
     */
    private $fecha_nacimiento;

    /**
     * @var string $dni
     *
     * @ORM\Column(name="dni", type="string", length=9)
     */
    private $dni;

    /**
     * @var string $numero_tarjeta
     *
     * @ORM\Column(name="numero_tarjeta", type="string", length=20)
     * @Assert\Regex("/\d{11,19}/")
     */
    private $numero_tarjeta;

    /**
     * @var integer $ciudad
     *
     * @ORM\ManyToOne(targetEntity="Cupon\CiudadBundle\Entity\Ciudad", inversedBy="usuarios")
     * @Assert\Type("Cupon\CiudadBundle\Entity\Ciudad")
     */
    private $ciudad;

    public function __construct()
    {
        $this->fecha_alta = new \DateTime();
    }

    public function __toString()
    {
        return $this->getNombre().' '.$this->getApellidos();
    }

    public function __sleep(){
        return array('id', 'nombre', 'email');
    }

    /**
     * Validador propio que comprueba si el DNI introducido es válido
     *
     * El DNI es un identificador único obligatorio para todos los ciudadanos de
     * España y de varios países americanos.
     *
     *   Formato:   entre 1 y 8 números seguidos de 1 letra
     *   Ejemplos:  12345678Z - 11111111H - 01234567L
     *
     * Los números se pueden escoger aleatoriamente, pero la letra depende de los
     * números y por tanto, actúa como carácter de control. ¿Cómo se obtiene la
     * letra a partir de los números?
     *
     *   1. Obtener el 'mod 23' (resto de la división entera) del número
     *      (e.g.: 12345678 mod 23 = 14).
     *   2. Utilizar la siguiente tabla para elegir la letra que corresponde al
     *      resultado de la operación anterior.
     *
     *   +--------+----+----+----+----+----+----+----+----+----+----+----+----+
     *   | mod 23 |  0 |  1 |  2 |  3 |  4 |  5 |  6 |  7 |  8 |  9 | 10 | 11 |
     *   +--------+----+----+----+----+----+----+----+----+----+----+----+----+
     *   | letra  |  T |  R |  W |  A |  G |  M |  Y |  F |  P |  D |  X |  B |
     *   +--------+----+----+----+----+----+----+----+----+----+----+----+----+
     *   | mod 23 | 12 | 13 | 14 | 15 | 16 | 17 | 18 | 19 | 20 | 21 | 22 |    |
     *   +--------+----+----+----+----+----+----+----+----+----+----+----+----+
     *   | letra  |  N |  J |  Z |  S |  Q |  V |  H |  L |  C |  K |  E |    |
     *   +--------+----+----+----+----+----+----+----+----+----+----+----+----+
     *   
     */
    public function esDniValido(ExecutionContextInterface $context)
    {
        $dni = $this->getDni();

        // Comprobar que el formato sea correcto
        if (0 === preg_match("/\d{1,8}[a-z]/i", $dni)) {
            $context->addViolationAt('dni', 'El DNI introducido no tiene el formato correcto (entre 1 y 8 números seguidos de una letra, sin guiones y sin dejar ningún espacio en blanco)');

            return;
        }

        // Comprobar que la letra cumple con el algoritmo
        $numero = substr($dni, 0, -1);
        $letra  = strtoupper(substr($dni, -1));
        if ($letra != substr("TRWAGMYFPDXBNJZSQVHLCKE", strtr($numero, "XYZ", "012")%23, 1)) {
            $context->addViolationAt('dni', 'La letra no coincide con el número del DNI. Comprueba que has escrito bien tanto el número como la letra');
        }
    }

    /**
     * @Assert\True(message = "Debes tener al menos 18 años para registrarte en el sitio")
     */
    public function isMayorDeEdad()
    {
        return $this->fecha_nacimiento <= new \DateTime('today - 18 years');
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set apellidos
     *
     * @param string $apellidos
     */
    public function setApellidos($apellidos)
    {
        $this->apellidos = $apellidos;
    }

    /**
     * Get apellidos
     *
     * @return string
     */
    public function getApellidos()
    {
        return $this->apellidos;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set direccion
     *
     * @param text $direccion
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    }

    /**
     * Get direccion
     *
     * @return text
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set permite_email
     *
     * @param boolean $permiteEmail
     */
    public function setPermiteEmail($permiteEmail)
    {
        $this->permite_email = $permiteEmail;
    }

    /**
     * Get permite_email
     *
     * @return boolean
     */
    public function getPermiteEmail()
    {
        return $this->permite_email;
    }

    /**
     * Set fecha_alta
     *
     * @param datetime $fechaAlta
     */
    public function setFechaAlta($fechaAlta)
    {
        $this->fecha_alta = $fechaAlta;
    }

    /**
     * Get fecha_alta
     *
     * @return datetime
     */
    public function getFechaAlta()
    {
        return $this->fecha_alta;
    }

    /**
     * Set fecha_nacimiento
     *
     * @param datetime $fechaNacimiento
     */
    public function setFechaNacimiento($fechaNacimiento)
    {
        $this->fecha_nacimiento = $fechaNacimiento;
    }

    /**
     * Get fecha_nacimiento
     *
     * @return datetime
     */
    public function getFechaNacimiento()
    {
        return $this->fecha_nacimiento;
    }

    /**
     * Set dni
     *
     * @param string $dni
     */
    public function setDni($dni)
    {
        $this->dni = $dni;
    }

    /**
     * Get dni
     *
     * @return string
     */
    public function getDni()
    {
        return $this->dni;
    }

    /**
     * Set numero_tarjeta
     *
     * @param string $numeroTarjeta
     */
    public function setNumeroTarjeta($numeroTarjeta)
    {
        $this->numero_tarjeta = $numeroTarjeta;
    }

    /**
     * Get numero_tarjeta
     *
     * @return string
     */
    public function getNumeroTarjeta()
    {
        return $this->numero_tarjeta;
    }

    /**
     * Set ciudad
     *
     * @param Cupon\CiudadBundle\Entity\Ciudad $ciudad
     */
    public function setCiudad(\Cupon\CiudadBundle\Entity\Ciudad $ciudad)
    {
        $this->ciudad = $ciudad;
    }

    /**
     * Get ciudad
     *
     * @return Cupon\CiudadBundle\Entity\Ciudad
     */
    public function getCiudad()
    {
        return $this->ciudad;
    }
}
