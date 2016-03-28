<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * Muestra el formulario de contacto y también procesa el envío de emails.
     *
     * @Route("/contacto", defaults={ "_locale"="es" }, name="contacto")
     */
    public function contactoAction(Request $request)
    {
        // Se crea un formulario "in situ", sin clase asociada
        $formulario = $this->createFormBuilder()
            ->add('remitente', 'Symfony\Component\Form\Extension\Core\Type\EmailType', array('label' => 'Tu dirección de email'))
            ->add('mensaje', 'Symfony\Component\Form\Extension\Core\Type\TextareaType')
            ->add('enviar', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', array('label' => 'Enviar mensaje'))
            ->getForm()
        ;

        $formulario->handleRequest($request);

        if ($formulario->isValid()) {
            $datos = $formulario->getData();

            $contenido = sprintf(" Remitente: %s \n\n Mensaje: %s \n\n Navegador: %s \n Dirección IP: %s \n",
                $datos['remitente'],
                htmlspecialchars($datos['mensaje']),
                $request->server->get('HTTP_USER_AGENT'),
                $request->server->get('REMOTE_ADDR')
            );

            $mensaje = \Swift_Message::newInstance()
                ->setSubject('Contacto')
                ->setFrom($datos['remitente'])
                ->setTo('contacto@cupon')
                ->setBody($contenido)
            ;

            $this->container->get('mailer')->send($mensaje);
            $this->get('session')->setFlash('info', 'Tu mensaje se ha enviado correctamente.');

            return $this->redirectToRoute('portada');
        }

        return $this->render('sitio/contacto.html.twig', array(
            'formulario' => $formulario->createView(),
        ));
    }
}
