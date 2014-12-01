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
        // laadt Doctrine manager
        $em = $this->getDoctrine()->getManager();
        
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt de user
        //$user = \vino\PillarBundle\Functions\ServiceCollection::checkKlant($session, $em);
        $user = $this->getUser();
        
        $mandje = $session->get('mandje');
        
        return $this->render('vinoPillarBundle:Default:index.html.twig', array('user' => $user, 'mandje' => $mandje));
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
        // laadt Doctrine manager
        $em = $this->getDoctrine()->getManager();
        
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt de user
        $user = \vino\PillarBundle\Functions\ServiceCollection::checkKlant($session, $em);
        
        return $this->render('vinoPillarBundle:Default:overons.html.twig', array('user' => $user,));
    }
    
    public function contactAction(Request $request) {
        // laadt Doctrine manager
        $em = $this->getDoctrine()->getManager();
        
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt de user
        $user = \vino\PillarBundle\Functions\ServiceCollection::checkKlant($session, $em);
        
        return $this->render('vinoPillarBundle:Default:contact.html.twig', array('user' => $user,));
    }
    /*
    public function loginAction(Request $request) {
        // laadt Doctrine manager
        $em = $this->getDoctrine()->getManager();
        
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt de user
        $user = \vino\PillarBundle\Functions\ServiceCollection::checkKlant($session, $em);
        
        if ($user->getId() == 1) {
            // REGISTRATIEFORM
            $klant = new Klant();
            $klant->setLevel(0);

            $reg_form = $this->createFormBuilder($klant)
                    ->setAction($this->generateUrl('vino_pillar_register_process'))
                    ->add('vNaam', 'text', array (
                        'label' => 'Voornaam',
                        'attr' => array('class' => 'form-control')
                        ))
                    ->add('aNaam', 'text', array (
                        'label' => 'Familienaam',
                        'attr' => array('class' => 'form-control')
                        ))
                    ->add('straat', 'text', array (
                        'attr' => array('class' => 'form-control')
                        ))
                    ->add('huisnr', 'text', array (
                        'label' => 'Huisnummer',
                        'attr' => array('class' => 'form-control')
                        ))
                    ->add('busnr', 'text', array (
                        'label' => 'Busnummer',
                        'required' => false,
                        'attr' => array('class' => 'form-control')
                        ))
                    ->add('postcode', 'text', array (
                        'attr' => array('class' => 'form-control')
                        ))
                    ->add('gemeente', 'text', array (
                        'attr' => array('class' => 'form-control')
                        ))
                    ->add('email', 'email', array (
                        'attr' => array('class' => 'form-control')
                        ))
                    ->add('paswoord', 'password', array (
                        'attr' => array('class' => 'form-control')
                        ))
                    ->add('save', 'submit', array('label' => 'Registreer'))
                    ->getForm();

            $reg_form_view = $reg_form->createView();
            
            
            // LOGINFORM
            $loginklant = new Klant();
            
            $login_form = $this->createFormBuilder($loginklant)
                    ->setAction($this->generateUrl('vino_pillar_login_process'))
                    ->add('email', 'email', array (
                        'attr' => array('class' => 'form-control')
                        ))
                    ->add('paswoord', 'password', array (
                        'attr' => array('class' => 'form-control')
                        ))
                    ->add('save', 'submit', array('label' => 'Login'))
                    ->getForm();
            
            $login_form_view = $login_form->createView();
            
        } else {
            $reg_form_view = null;
            $login_form_view = null;
        }
        
        return $this->render('vinoPillarBundle:Default:user.html.twig', array(
            'user' => $user,
            'reg_form' => $reg_form_view,
            'login_form' => $login_form_view,
            'action' => 'login_start',
            ));
    }
    */
    
    public function loginAction(Request $request) {
        $session = $request->getSession();
        
        // als er reeds een user ingelogd is, wordt er geredirect naar de homepage
        if($this->getUser()) {
            //return $this->redirect($this->generateUrl('vino_pillar_homepage'));
            $url = $this->getRequest()->headers->get("referer");
            return $this->redirect($url);
        }
        
        // get the login error if there is one
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContextInterface::AUTHENTICATION_ERROR
            );
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }
        
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);
        

        return $this->render(
            'vinoPillarBundle:Default:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );
    }
    
    public function loginProcessAction(Request $request) {
        // laadt Doctrine manager
        $em = $this->getDoctrine()->getManager();
        
        // laadt de sessie
        $session = $request->getSession();
        
        $klant = new Klant();
        
        $form = $this->createFormBuilder($klant)
                ->add('email', 'email')
                ->add('paswoord', 'password')
                ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isValid()) {
            // check of de klant bestaat
            $echteklant = KlantController::loginKlant($klant->getEmail(), $klant->getPaswoord());
            if ($echteklant) {
                $dezeklant = $em->getRepository('vinoPillarBundle:Klant')->findByEmail($klant->getEmail());
                $session->set('user', $dezeklant);
                // geef de melding weer
                $infoMsg = 'Succesvol ingelogd!';
                $this->get('session')
                        ->getFlashBag()
                        ->add('infomsg', $infoMsg);
            } else {
                $dezeklant = null;
                // geef de melding weer
                $infoMsg = 'Verkeerd paswoord of verkeerde gebruiker';
                $this->get('session')
                        ->getFlashBag()
                        ->add('infomsg', $infoMsg);
            }
        } else {
            // geef de melding weer
                $infoMsg = 'Uw formulier was niet volledig!';
                $this->get('session')
                        ->getFlashBag()
                        ->add('infomsg', $infoMsg);
            $dezeklant = null;
        }
        
        return $this->render('vinoPillarBundle:Default:user.html.twig', array(
            'login_form' => null,
            'reg_form' => null,
            'user' => $dezeklant,
            ));
    }
    
    public function registerProcessAction(Request $request) {
        
        $klant = new Klant();

        $form = $this->createFormBuilder($klant)
                ->add('vNaam', 'text', array (
                    'label' => 'Voornaam',
                    'attr' => array('class' => 'form-control')
                    ))
                ->add('aNaam', 'text', array (
                    'label' => 'Familienaam',
                    'attr' => array('class' => 'form-control')
                    ))
                ->add('straat', 'text', array (
                    'attr' => array('class' => 'form-control')
                    ))
                ->add('huisnr', 'text', array (
                    'label' => 'Huisnummer',
                    'attr' => array('class' => 'form-control')
                    ))
                ->add('busnr', 'text', array (
                    'label' => 'Busnummer',
                    'required' => false,
                    'attr' => array('class' => 'form-control')
                    ))
                ->add('postcode', 'text', array (
                    'attr' => array('class' => 'form-control')
                    ))
                ->add('gemeente', 'text', array (
                    'attr' => array('class' => 'form-control')
                    ))
                ->add('email', 'email', array (
                    'attr' => array('class' => 'form-control')
                    ))
                ->add('paswoord', 'password', array (
                    'attr' => array('class' => 'form-control')
                    ))
                ->add('save', 'submit', array('label' => 'Registreer'))
                ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            // perform some action, such as saving the task to the database
            $em = $this->getDoctrine()->getManager();
            $em->persist($klant);
            $em->flush();
            
            return $this->render('vinoPillarBundle:Default:user.html.twig');
        }
    }
    
    public function logoutAction(Request $request) {
        $session = $request->getSession();
        $session->set('mandje', null);
        return $this->redirect($this->generateUrl('vino_pillar_homepage'));
    }
}
