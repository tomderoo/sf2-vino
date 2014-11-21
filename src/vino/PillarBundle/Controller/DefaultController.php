<?php
// src/vino/PillarBundle/Controller/DefaultController.php

namespace vino\PillarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $session = $request->getSession();
        
        if (!$session->get('user')) {
            // no user exists
            $user = "anon";
            $session->set('user', $user);
        } else {
            $user = $session->get('user');
        }
        
        //$user = "info@tomderoo.eu";
        
        return $this->render('vinoPillarBundle:Default:index.html.twig', array('user' => $user,));
    }
    
    public function wijnenAction(Request $request) {
        // naar de wijncollectiebundle?
    }
    
    public function wijndetailAction(Request $request, $slug) {
        // toont een wijndetail op basis van een slug
        
        // zoek het wijndetail in de database op basis van de slug
        $product = null;
        
        // indien de slug niet kan worden gekoppeld aan de database
        if (!$product) {
            // toont een foutmelding...
            // throw $this->createNotFoundException('Sorry! Dit item konden we niet terugvinden...');
            // maar we kunnen alternatief ook een redirect doen naar een andere pagina
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        }
        
        // toon het wijndetail
        return $this->render('vinoPillarBundle:Default:productdetail.html.twig');
    }
    
    public function overonsAction(Request $request) {
        $session = $request->getSession();
        
        if (!$session->get('user')) {
            // no user exists
            $user = "anon";
            $session->set('user', $user);
        } else {
            $user = $session->get('user');
        }
        
        return $this->render('vinoPillarBundle:Default:overons.html.twig', array('user' => $user,));
    }
    
    public function contactAction(Request $request) {
        $session = $request->getSession();
        
        if (!$session->get('user')) {
            // no user exists
            $user = "anon";
            $session->set('user', $user);
        } else {
            $user = $session->get('user');
        }

        return $this->render('vinoPillarBundle:Default:contact.html.twig', array('user' => $user,));
    }
    
    public function loginAction(Request $request) {
        return $this->render('vinoPillarBundle:Default:user.html.twig', array('action' => 'login_start'));
    }
    
    public function logoutAction(Request $request) {
        return $this->render('vinoPillarBundle:Default:user.html.twig', array('action' => 'logout_start'));
    }
}
