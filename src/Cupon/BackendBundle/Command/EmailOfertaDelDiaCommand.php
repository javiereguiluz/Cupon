<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace Cupon\BackendBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Comando que envía cada día un email a todos los usuarios que lo
 * permiten con la información de la oferta del día en su ciudad
 *
 */
class EmailOfertaDelDiaCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('email:oferta-del-dia')
            ->setDefinition(array(
                new InputOption('accion', false, InputOption::VALUE_OPTIONAL, 'Indica si los emails realmente se envían a sus destinatarios o sólo se generan')
            ))
            ->setDescription('Genera y envía a cada usuario el email con la oferta diaria')
            ->setHelp(<<<EOT
El comando <info>email:oferta-del-dia</info> genera y envía un email con la
oferta del día de la ciudad en la que se ha apuntado el usuario. También tiene
en cuenta si el usuario permite el envío o no de publicidad.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $host = 'dev' == $input->getOption('env') ? 'http://cupon.local' : 'http://cupon.com';
        $accion = $input->getOption('accion');

        $contenedor = $this->getContainer();
        $em = $contenedor->get('doctrine')->getManager();

        // Obtener el listado de usuarios que permiten el envío de email
        $usuarios = $em->getRepository('UsuarioBundle:Usuario')->findBy(array('permite_email' => true));

        $output->writeln(sprintf(' Se van a enviar <info>%s</info> emails', count($usuarios)));

        // Buscar la 'oferta del día' en todas las ciudades de la aplicación
        $ofertas = array();
        $ciudades = $em->getRepository('CiudadBundle:Ciudad')->findAll();
        foreach ($ciudades as $ciudad) {
            $id   = $ciudad->getId();
            $slug = $ciudad->getSlug();

            $ofertas[$id] = $em->getRepository('OfertaBundle:Oferta')->findOfertaDelDiaSiguiente($slug);
        }

        // Generar el email personalizado de cada usuario
        foreach ($usuarios as $usuario) {
            $ciudad = $usuario->getCiudad();
            $oferta = $ofertas[$ciudad->getId()];

            $contenido = $contenedor->get('twig')->render(
                'BackendBundle:Oferta:email.html.twig',
                array('host' => $host, 'ciudad' => $ciudad, 'oferta' => $oferta, 'usuario' => $usuario)
            );

            // Enviar el email
            if ('enviar' == $accion) {
                $email = \Swift_Message::newInstance()
                    ->setSubject($oferta->getNombre().' en '.$oferta->getTienda()->getNombre())
                    ->setFrom(array('oferta-del-dia@cupon.com' => 'Cupon - Oferta del día'))
                    ->setTo($usuario->getEmail())
                    ->setBody($contenido, 'text/html')
                ;
                $this->getContainer()->get('mailer')->send($email);
            }
        }

        if ('enviar' != $accion) {
            $output->writeln("\n No se ha enviado ningún email. Para enviar los emails a sus destinatarios,\n ejecuta el comando con la opción <info>accion</info>. Ejemplo:\n <info>./app/console email:oferta-del-dia --accion=enviar</info>\n");
        }
    }
}
