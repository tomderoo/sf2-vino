<?php
// src/vino/PillarBundle/Controller/KlantController.php

namespace vino\PillarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use vino\PillarBundle\Entity\Klant;
use vino\PillarBundle\Entity\Review;

use vino\PillarBundle\Form\Type\KlantType;

class KlantController extends Controller {
    
    /* * * LOGIN KLANT : logt de klant in * * */
    /*
    public function loginKlant($email, $paswoord) {
        $manager = $this->getDoctrine()->getManager();
        
        $db_klant = $manager->getRepository('vinoPillarBundle:Klant')->findOneByEmail($email);
        
        if (!$db_klant) {
            // klant niet gevonden!
            return false;
        }
        
        if ($db_klant->getPaswoord() != $paswoord) {
            // paswoord verkeerd!
            return false;
        } else {
            // klant is succesvol ingelogd
            return true;
        }
    }
    */
    
    /* * * REGISTER : nieuwe klant registreren * * */
    
    public function registerAction(Request $request) {
        // als er reeds een user ingelogd is, wordt er geredirect
        // DIT WERKT NIET INDIEN DE LOGIN ACHTER EEN ANDERE FIREWALL ZIT!
        // Er bestaat dan enkel de anonymous user, ongeacht of er een user
        // is ingelogd in de andere context... Daarom: firewall samenvoegen
        if($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') === true) {
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
            //$url = $this->getRequest()->headers->get("referer");
            //return $this->redirect($url);
        }

        // maak de entity voor het form
        $klant = new Klant();
        // maak een form op basis van het klanttype
        $form = $this->createForm(new KlantType(), $klant, array('attr' => array('class' => 'form-horizontal')));
        
        $form->handleRequest($request);
        
        // indien form valid ingediend
        if ($form->isValid()) {
            // perform some action, such as saving the task to the database
            $paswoord = $klant->getPaswoord();
            $crypted = password_hash($paswoord, PASSWORD_BCRYPT, array('cost' => 8));
            $klant->setPaswoord($crypted);
            $klant->setIsActive(1);
            $klant->setLevel(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($klant);
            $em->flush();
            
            // geef succesmelding weer en redirect
            $msg_success = 'U bent succesvol geregistreerd! U kan nu <a href="' . $this->generateUrl('login') . '">inloggen</a> onder uw e-mailadres en gekozen paswoord.';
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_success', $msg_success);
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        }
        
        $form_view = $form->createView();
        
        return $this->render('vinoPillarBundle:Klant:register.html.twig', array(
            'form' => $form_view,
                ));
    }
    
    /* * * BEKIJK KLANT : toont klantgegevens * * */
    /*
    public function bekijkKlantAction(Request $request) {
        // checken of er wel een user is ingelogd, anders redirect
        if (!$this->getUser()) {
            $infoMsg = "Foutieve aanroep van de klantpagina";
            // maak flashmessage
            $this->get('session')
                    ->getFlashBag()
                    ->add('infomsg', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        } else {
            $user = $this->getUser();
        }
        
        return $this->render('vinoPillarBundle:Klant:klant.html.twig', array(
            'user' => $user,
        ));
    }
    */
    /* * * * * DUMMY ACTIES : om testgegevens te maken * * * * */
    public function maakDummyKlant() {
        $manager = $this->getDoctrine()->getManager();
        
        // maak de klant
        $klant = new Klant();
        $klant->setVNaam('Dummy');
        $klant->setANaam('Dumnuts');
        $klant->setStraat('Nietteslimplein');
        $klant->setHuisnr('12');
        $klant->setBusnr('A5');
        $klant->setPostcode('2000');
        $klant->setGemeente('Antwerpen');
        $klant->setEmail('dummy@dumnuts.be');
        $klant->setPaswoord('dummyneedsnopassword');
        $klant->setLevel(0);
        
        // persisten en flushen
        $manager->persist($klant);
        $manager->flush();
        
        // return de klant
        return $klant;
    }
    
    public function dummyklantAction() {
        $manager = $this->getDoctrine()->getManager();
        
        // test de klant
        $test = $manager->getRepository('vinoPillarBundle:Klant')->findOneByVNaam('Dummy');
        if ($test) {
            $infoMsg = "De dummyklant bestaat reeds!";
            // maak flashmessage
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_error', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        } else {
            KlantController::maakDummyKlant();
        }
        
        // maak flashmessage
        $infoMsg = 'Klant Dummy Dumnuts aangemaakt!';
        $this->get('session')
                ->getFlashBag()
                ->add('msg_info', $infoMsg);
        return $this->redirect($this->generateUrl('vino_pillar_homepage'));
    }
    
    public function dummyreviewAction($slug) {
        $manager = $this->getDoctrine()->getManager();
        
        // test of er een dummyklant is
        $dummyklant = $manager->getRepository('vinoPillarBundle:Klant')->findOneByVNaam('Dummy');
        if (!$dummyklant) {
            $dummyklant = KlantController::maakDummyKlant();
        }
        
        // zoek de bijhorende wijn voor deze review
        $wijn = $manager->getRepository('vinoPillarBundle:Wijn')->findOneBySlug($slug);
        
        // maak de review
        $review = new Review();
        $review->setKlant($dummyklant);
        $review->setWijn($wijn);
        $review->setDatum(new \DateTime());
        $review->setTitel('Dummyreview voor deze wijn');
        $review->setTekst('Man man man is me dat een machtig wijntje!');
        $review->setRating(4);
        
        // persisten en flushen
        $manager->persist($review);
        $manager->flush();
        
        // maak flashmessage
        $infoMsg = 'Review aangemaakt!';
        $this->get('session')
                ->getFlashBag()
                ->add('msg_success', $infoMsg);
        return $this->redirect($this->generateUrl('vino_pillar_wijndetail', array('slug' => $slug,)));
    }
    
}