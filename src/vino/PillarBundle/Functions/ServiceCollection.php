<?php

namespace vino\PillarBundle\Functions;

class ServiceCollection {
    
    /* deze functie kijkt of er in de meegeleverde sessie een klant ingesteld is
     * @ session: de sessie
     * @ em: de event-manager
     */
    public static function checkKlant($session, $em) {
        if (!$session->get('user')) {
            // er bestaat geen user in de sessie
            // dus maken we een standaarduser uit de dbase op id 1 (de dummy)
            $user = $em->getRepository('vinoPillarBundle:Klant')->findOneById(1);   // dit laadt de dummy user
            $session->set('user', $user);
        } else {
            //$user = $em->getRepository('vinoPillarBundle:Klant')->findOneById(1); // DEBUG bij geval van foute sessie
            // er bestaat wel een user in de sessie, dus gaan we die laden
            $user = $session->get('user');
        }
        return $user;
    }
    
    public static function setMsg_Success($container, $msg) {
        $container->get('session')->getFlashBag()->add('msg_success', $msg);
    }
    
}