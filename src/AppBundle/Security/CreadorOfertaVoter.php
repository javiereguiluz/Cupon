<?php

/*
 * (c) Javier Eguiluz <javier.eguiluz@gmail.com>
 *
 * Este archivo pertenece a la aplicación de prueba Cupon.
 * El código fuente de la aplicación incluye un archivo llamado LICENSE
 * con toda la información sobre el copyright y la licencia.
 */

namespace AppBundle\Security;

use AppBundle\Entity\Oferta;
use AppBundle\Entity\Tienda;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Este voter decide si la oferta puede ser editada por la tienda actualmente logueada.
 */
class CreadorOfertaVoter extends Voter
{
    public function supports($attribute, $subject)
    {
        return $subject instanceof Oferta && 'ROLE_EDITAR_OFERTA' === $attribute;
    }

    /**
     * Devuelve 'true' si el usuario logueado es de tipo Tienda y es el creador
     * de la oferta que se quiere modificar.
     *
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $tienda = $token->getUser();

        return $tienda instanceof Tienda && $subject->getTienda()->getId() === $tienda->getId();
    }
}
