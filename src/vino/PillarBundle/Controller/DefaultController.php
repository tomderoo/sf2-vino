<?php
// src/vino/PillarBundle/Controller/DefaultController.php

namespace vino\PillarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;   // nodig om login te kunnen behandelen

use vino\PillarBundle\Entity\Klant;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt de user
        //$user = \vino\PillarBundle\Functions\ServiceCollection::checkKlant($session, $em);
        $user = $this->getUser();
        $mandje = $session->get('mandje');
        
        return $this->render('vinoPillarBundle:Default:index.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
                ));
    }
    
    public function overonsAction(Request $request) {
        // laadt Doctrine manager
        $em = $this->getDoctrine()->getManager();
        
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt de user
        //$user = \vino\PillarBundle\Functions\ServiceCollection::checkKlant($session, $em);
        $user = $this->getUser();
        $mandje = $session->get('mandje');
        
        return $this->render('vinoPillarBundle:Default:overons.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
            ));
    }
    
    public function contactAction(Request $request) {
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt de user
        $user = $this->getUser();
        $mandje = $session->get('mandje');
        
        return $this->render('vinoPillarBundle:Default:contact.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
            ));
    }
    
    public function loginAction(Request $request) {
        $session = $request->getSession();
        
        // als er reeds een user ingelogd is, wordt er geredirect
        // DIT WERKT NIET INDIEN DE LOGIN ACHTER EEN ANDERE FIREWALL ZIT!
        // Er bestaat dan enkel de anonymous user, ongeacht of er een user
        // is ingelogd in de andere context... Daarom: firewall samenvoegen
        if($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') === true) {
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
            //$url = $this->getRequest()->headers->get("referer");
            //return $this->redirect($url);
        }
        
        // get the login error if there is one
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            /*$msg_error = $request->attributes->get(
                SecurityContextInterface::AUTHENTICATION_ERROR
            )->getMessage();*/
            $msg_error = "Foute gebruikersnaam of fout paswoord - probeer opnieuw.";
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            //$msg_error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR)->getMessage();
            $msg_error = "Foute gebruikersnaam of fout paswoord - probeer opnieuw.";
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $msg_error = null;
        }
        
        if ($msg_error) {
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_error', $msg_error);
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);
        
        return $this->render(
            'vinoPillarBundle:Default:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
            )
        );
    }
}
