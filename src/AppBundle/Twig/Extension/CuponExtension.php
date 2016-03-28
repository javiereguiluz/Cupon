<?php

namespace AppBundle\Twig\Extension;

/**
 * Extensión propia de Twig con filtros y funciones útiles para
 * la aplicación.
 */
class CuponExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('mostrar_como_lista', array($this, 'mostrarComoLista'), array('is_safe' => array('html'))),
            new \Twig_SimpleFilter('cuenta_atras', array($this, 'cuentaAtras'), array('is_safe' => array('html'))),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('descuento', array($this, 'descuento')),
        );
    }

    /**
     * Muestra como una lista HTML el contenido de texto al que se
     * aplica el filtro. Cada "\n" genera un nuevo elemento de
     * la lista.
     *
     * @param string $value El texto que se transforma
     * @param string $tipo  Tipo de lista a generar ('ul', 'ol')
     *
     * @return string
     */
    public function mostrarComoLista($value, $tipo = 'ul')
    {
        $html = '<'.$tipo.'>'.PHP_EOL;
        $html .= '  <li>'.str_replace(PHP_EOL, '</li>'.PHP_EOL.'  <li>', $value).'</li>'.PHP_EOL;
        $html .= '</'.$tipo.'>'.PHP_EOL;

        return $html;
    }

    /**
     * Transforma una fecha en una cuenta atrás actualizada en tiempo
     * real mediante JavaScript.
     *
     * La cuenta atrás se muestra en un elemento HTML con un atributo
     * `id` generado automáticamente, para que se puedan añadir varias
     * cuentas atrás en la misma página.
     *
     * @param \DateTime $fecha Objeto que representa la fecha original
     *
     * @return string
     */
    public function cuentaAtras(\DateTime $fecha)
    {
        // En JavaScript los meses empiezan a contar en 0 y acaban en 12
        // En PHP los meses van de 1 a 12, por lo que hay que convertir la fecha
        $fechaJson = json_encode(array(
            'ano' => $fecha->format('Y'),
            'mes' => $fecha->format('m') - 1,
            'dia' => $fecha->format('d'),
            'hora' => $fecha->format('H'),
            'minuto' => $fecha->format('i'),
            'segundo' => $fecha->format('s'),
        ));

        $idAleatorio = 'cuenta-atras-'.mt_rand(1, 100000);
        $html = <<<EOJ
        <span id="$idAleatorio"></span>

        <script type="text/javascript">
        funcion_expira = function(){
            var expira = $fechaJson;
            muestraCuentaAtras('$idAleatorio', expira);
        }
        if (!window.addEventListener) {
            window.attachEvent("onload", funcion_expira);
        } else {
            window.addEventListener('load', funcion_expira);
        }
        </script>
EOJ;

        return $html;
    }

    /**
     * Calcula el porcentaje que supone el descuento indicado en euros.
     * El precio no es el precio original sino el precio de venta (también en euros).
     *
     * @param string     $precio    Precio de venta del producto (en euros)
     * @param string     $descuento Descuento sobre el precio original (en euros)
     * @param int|string $decimales Número de decimales que muestra el descuento
     *
     * @return string
     */
    public function descuento($precio, $descuento, $decimales = 0)
    {
        if (!is_numeric($precio) || !is_numeric($descuento)) {
            return '-';
        }

        if ($descuento === 0 || $descuento === null) {
            return '0%';
        }

        $precio_original = $precio + $descuento;
        $porcentaje = ($descuento / $precio_original) * 100;

        return '-'.number_format($porcentaje, $decimales).'%';
    }

    public function getName()
    {
        return 'cupon';
    }
}
