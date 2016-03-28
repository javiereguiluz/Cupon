<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Comando que envía cada día un email a todos los usuarios que lo
 * permiten con la información de la oferta del día en su ciudad.
 */
class EmailOfertaDelDiaCommand extends ContainerAwareCommand
{
    private $host;
    private $accion;
    /** @var ContainerInterface */
    private $contenedor;
    /** @var ObjectManager */
    private $em;
    /** @var SymfonyStyle */
    private $io;

    protected function configure()
    {
        $this
            ->setName('app:email:oferta-del-dia')
            ->setDefinition(array(
                new InputOption('accion', false, InputOption::VALUE_OPTIONAL, 'Indica si los emails realmente se envían a sus destinatarios o sólo se generan'),
            ))
            ->setDescription('Genera y envía a cada usuario el email con la oferta diaria')
            ->setHelp(<<<EOT
El comando <info>email:oferta-del-dia</info> genera y envía un email con la
oferta del día de la ciudad en la que se ha apuntado el usuario. También tiene
en cuenta si el usuario permite el envío o no de publicidad.
EOT
        );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->host = 'dev' === $input->getOption('env') ? 'http://cupon.local' : 'http://cupon.com';
        $this->accion = $input->getOption('accion');
        $this->contenedor = $this->getContainer();
        $this->em = $this->contenedor->get('doctrine')->getManager();
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Mailing - Oferta del día');

        $destinatarios = $this->em->getRepository('AppBundle:Usuario')->findBy(array('permiteEmail' => true));
        $this->io->text(sprintf('Se van a enviar <info>%d</info> emails', count($destinatarios)));

        $ofertasDelDia = $this->findOfertasDelDia();
        foreach ($destinatarios as $destinatario) {
            $ciudad = $destinatario->getCiudad();
            $oferta = $ofertasDelDia[$ciudad->getId()];

            $contenido = $this->contenedor->get('twig')->render('email/oferta_del_dia.html.twig', array(
                'host' => $this->host,
                'ciudad' => $ciudad,
                'oferta' => $oferta,
                'usuario' => $destinatario,
            ));

            $asunto = sprintf('[Oferta del día] %s en %s', $oferta->getNombre(), $oferta->getTienda()->getNombre());
            $this->enviarEmail($destinatario, $asunto, $contenido);
        }

        if ('enviar' !== $this->accion) {
            $this->io->comment(array(
                'NOTA: No se ha enviado ningún email.',
                'Para enviar los emails a sus destinatarios, ejecuta el comando con la opción <info>accion</info>.',
                'Ejemplo: <info>./app/console email:oferta-del-dia --accion=enviar</info>',
            ));
        }

        $this->io->success(sprintf('%d emails enviados con la oferta del día', count($destinatarios)));
    }

    /**
     * Busca la 'oferta del día' en todas las ciudades de la aplicación.
     *
     * @return array
     */
    private function findOfertasDelDia()
    {
        $ofertas = array();
        $ciudades = $this->em->getRepository('AppBundle:Ciudad')->findAll();
        foreach ($ciudades as $ciudad) {
            $id = $ciudad->getId();
            $slug = $ciudad->getSlug();

            $ofertas[$id] = $this->em->getRepository('AppBundle:Oferta')->findOfertaDelDiaSiguiente($slug);
        }

        return $ofertas;
    }

    /**
     * @param string $destinatario
     * @param string $asunto
     * @param string $contenido
     */
    private function enviarEmail($destinatario, $asunto, $contenido)
    {
        if ('enviar' === $this->accion) {
            $email = \Swift_Message::newInstance()
                ->setSubject($asunto)
                ->setFrom(array('oferta-del-dia@cupon.com' => 'Cupon - Oferta del día'))
                ->setTo($destinatario->getEmail())
                ->setBody($contenido, 'text/html')
            ;

            $this->contenedor->get('mailer')->send($email);
        }
    }
}
