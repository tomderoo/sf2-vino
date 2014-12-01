<?php
// src/vino/PillarBundle/Controller/MandjeController.php

namespace vino\PillarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use vino\PillarBundle\Entity\Bestelling;
use vino\PillarBundle\Entity\Verpakkinglijn;
use vino\PillarBundle\Entity\Bestellijn;
use \stdClass;

class MandjeController extends Controller {
    
    
    /* * * * * ACTIES * * * * */
    
    /* * * BEKIJK MANDJE: bekijk het mandje in een aparte pagina * * */
    
    public function bekijkMandjeAction(Request $request) {
        $session = $request->getSession();
        
        $user = $this->getUser();
        
        // checken of het mandje uberhaupt bestaat, indien niet: geef leeg mandje weer
        if(!$session->get('mandje')) {
            // het mandje is leeg
            return $this->render('vinoPillarBundle:Mandje:mandje.html.twig', array(
                'user' => $user,
                'mandje' => null,
            ));
        }
        
        $mandje = $session->get('mandje');
        
        // als het mandje bestaat, maar er zitten geen productlijnen in: zet op null, geef leeg weer
        if (!($mandje->getBestellijn()) or count($mandje->getBestellijn()) === 0) {
            $session->set('mandje', null);
        }
        
        // als het mandje derhalve niet leeg is, laadt het en geef verpakkingsopties
        if ($session->get('mandje')) {
            $verpakkinglijst = $this->verpakkingLijst();
        } else {
            $verpakkinglijst = null;
        }
        
        return $this->render('vinoPillarBundle:Mandje:mandje.html.twig', array(
                'user' => $user,
                'mandje' => $session->get('mandje'),
                'verpakkinglijst' => $verpakkinglijst,
            ));
    }
    
    /* * * LEDIG MANDJE: zegt het zelf * * */
    
    public function ledigMandjeAction(Request $request) {
        $session = $request->getSession();
        
        $user = $this->getUser();
        
        $session->set('mandje', null);
        
        // maak flashmessage
        $infoMsg = 'Uw mandje werd geledigd!';
        //$infoMsg = serialize($product);
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
        
        return $this->render('vinoPillarBundle:Mandje:mandje.html.twig', array(
                'user' => $user,
                'mandje' => null,
            ));
        
    }
    
    /* * * INBESTELLING: verwerkt het toevoegen van een item aan een mandje * * */
    
    public function inBestellingAction(Request $request, $slug) {
        $manager = $this->getDoctrine()->getManager();
        
        // test de slug
        $product = $manager->getRepository('vinoPillarBundle:Wijn')->findOneBySlug($slug);
        if (!$product) {
            $infoMsg = "De door u gekozen wijn kon niet worden teruggevonden en niet worden toegevoegd aan uw mandje!";
            // maak flashmessage
            $this->get('session')
                    ->getFlashBag()
                    ->add('infomsg', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_wijnen'));
        }
        
        // - - - voeg het product toe aan mandje
        
        // verifieer het mandje
        $session = $request->getSession();
        
        if (!$session->get('mandje')) {
            // er is nog geen mandje
            $mandje = new Bestelling();
            $session->set('mandje', $mandje);
        } else {
            $mandje = $session->get('mandje');
        }
        
        // check of het product reeds in het mandje zit
        $mandjeBestelling = $mandje;
        
        $found = false;
        
        foreach ($mandjeBestelling->getBestellijn() as $mandjelijn) {
            if ($mandjelijn->getWijn()->getId() == $product->getId()) {
                $aantal = $mandjelijn->getAantal();
                $aantal++;
                $mandjelijn->setAantal($aantal);
                $found = true;
                break;
            }
        }
        
        if(!$found) {
            $bestellijn = new Bestellijn();
            $bestellijn->setWijn($product);
            $bestellijn->setAantal(1);
            $bestellijn->setBestelling(null);
            $mandjeBestelling->addBestellijn($bestellijn);
        }
        
        $session->set('mandje', $mandjeBestelling);
        
        // maak flashmessage
        $infoMsg = 'Product toegevoegd aan mandje!';
        //$infoMsg = serialize($product);
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
        // laadt nieuwe pagina via redirect
        //return $this->redirect($this->generateUrl('vino_pillar_mandje'));
        // laadt dezelfde pagina via een reload/redirect
        $url = $this->getRequest()->headers->get("referer");
        return $this->redirect($url);
    }
    
    /* * * VERANDER AANTAL: verandert het aantal bestelde items op een lijn, pos of neg * * */
    
    public function mandjeAantalAction(Request $request, $slug, $aantal) {
        // verkrijg het mandje
        $session = $request->getSession();
        
        $mandje = $session->get('mandje');
        
        // opvragen van alle bestellijnen
        $bestellijnArray = $mandje->getBestellijn();
        
        // doorlopen van de bestellijnen op zoek naar de slug
        // gebruik van een foreach-lus om "missing indexes" te vermijden (anders moet de array telkens
        // gereset worden OF moet de ->removeBestellijn-functie worden herschreven in de entity
        foreach($bestellijnArray as $lijnID => $dezeLijn) {
            if ($dezeLijn->getWijn()->getSlug() == $slug) {
                $nuAantal = $dezeLijn->getAantal();
                $nieuwAantal = $nuAantal + $aantal;
                if ($nieuwAantal <= 0) {
                    $mandje->removeBestellijn($bestellijnArray[$lijnID]);
                } else {
                    $bestellijnArray[$lijnID]->setAantal($nieuwAantal);
                }
            }
        }
        
        // mandje terugzetten
        $session->set('mandje', $mandje);
        
        // maak flashmessage
        $infoMsg = 'Mandje aangepast!';
        //$infoMsg = serialize($product);
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
        
        return $this->redirect($this->generateUrl('vino_pillar_mandje'));
    }
    
    /* * * UIT BESTELLING: verwijdert een mandjelijn uit het mandje * * */
    
    public function uitBestellingAction(Request $request, $slug) {
        // verkrijg het mandje
        $session = $request->getSession();
        
        $mandje = $session->get('mandje');
        
        // zoek de bestellijn
        $bestellijnArray = $mandje->getBestellijn();
        
        // als het de enige bestellijn is, doe je gewoon een ledig-functie
        if (count($bestellijnArray) <= 1) {
            return $this->redirect($this->generateUrl('vino_pillar_ledig'));
        }
        
        // doorlopen van de bestellijnen op zoek naar de slug
        // gebruik van een foreach-lus om "missing indexes" te vermijden (anders moet de array telkens
        // gereset worden OF moet de ->removeBestellijn-functie worden herschreven in de entity
        foreach($bestellijnArray as $lijnID => $dezeLijn) {
            if ($dezeLijn->getWijn()->getSlug() == $slug) {
                $mandje->removeBestellijn($bestellijnArray[$lijnID]);
            }
        }
        
        //$mandje->setBestellijn($bestellijnArray); // dit moet blijkbaar niet? Hoe kan dit?
        $session->set('mandje', $mandje);
        
        // maak flashmessage
        $infoMsg = 'Productlijn verwijderd uit mandje!';
        //$infoMsg = serialize($product);
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
        
        return $this->redirect($this->generateUrl('vino_pillar_mandje'));
    }
    
    /* * * CHECKOUT: eerste fase voor het bevestigen van de bestelling * * */
    
    public function checkoutAction(Request $request) {
        // verkrijg het mandje
        $session = $request->getSession();
        
        // checken of er wel een mandje is om te checkout'en
        if (!$session->get('mandje') or count($session->get('mandje')->getBestellijn()) === 0) {
            // er is geen mandje-inhoud, dus redirect naar de indexpagina
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        } else {
            $mandje = $session->get('mandje');
        }
        
        // checken of er wel een user is om te checkout'en
        if(!$this->getUser()) {
            // er is geen user, dus redirect naar de loginpage
            return $this->redirect($this->generateUrl('login'));
        } else {
            $user = $this->getUser();
        }
        
        // toon de template
        return $this->render('vinoPillarBundle:Mandje:checkout.html.twig', array(
                'user' => $user,
                'mandje' => $mandje,
            'verpakkinglijst' => $this->verpakkingLijst(),
            ));
    }
    
    /* * * INVERPAKKING : voegt een verpakking toe aan de bestellijn OF verhoogt de bestelde verpakking * * */
    
    public function inVerpakkingAction (Request $request, $slug, $verpakking) {
        $em = $this->getDoctrine()->getManager();
        
        // laadt de sessie
        $session = $request->getSession();
        
        // zoek het mandje
        $mandje = $session->get('mandje');
        
        // - - - voeg de verpakkinglijn toe aan het mandje
        
        // check of het product reeds in het mandje zit
        $verpakkingFound = false;
        
        foreach ($mandje->getBestellijn() as $mandjelijn) {
            // we overlopen elke bestellijn in het mandje
            if ($mandjelijn->getWijn()->getSlug() == $slug) {
                // laadt de verpakking die bij de verpakking-id hoort
                $dezeVerpakking = $em->getRepository('vinoPillarBundle:Verpakking')->findOneById($verpakking);
                // dit is de juiste bestellijn
                foreach($mandjelijn->getVerpakkinglijn() as $verpakkinglijn) {
                    // we overlopen elke verpakkinglijn in de bestellijn
                    if ($verpakkinglijn->getVerpakking()->getId() === $dezeVerpakking->getId()) {
                        // de verpakkinglijn bestaat, en dit is ze
                        $nuAantal = $verpakkinglijn->getAantal();
                        $nuAantal++;
                        $verpakkinglijn->setAantal($nuAantal);
                        $verpakkingFound = true;
                        break;
                    }
                }
                if (!$verpakkingFound) {
                    // de verpakkinglijn voor dit type verpakking bestaat nog niet
                    $nieuweVerpakkinglijn = new Verpakkinglijn();
                    $nieuweVerpakkinglijn->setAantal(1);
                    $nieuweVerpakkinglijn->setBestellijn($mandjelijn->getId());
                    $nieuweVerpakkinglijn->setVerpakking($dezeVerpakking);
                    $mandjelijn->addVerpakkinglijn($nieuweVerpakkinglijn);
                }
            }
        }
        
        $session->set('mandje', $mandje);
        
        // maak flashmessage
        $infoMsg = 'Verpakking toegevoegd aan mandje!';
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
        // laadt mandje via redirect
        //return $this->redirect($this->generateUrl('vino_pillar_mandje'));
        // laadt dezelfde pagina via een reload/redirect
        $url = $this->getRequest()->headers->get("referer");
        return $this->redirect($url);
    }
    
    /* * * UITVERPAKKING : verlaagt een bestaande verpakking in de bestellijn OF verwijdert deze * * */
    
    public function uitVerpakkingAction (Request $request, $slug, $verpakking) {
        $em = $this->getDoctrine()->getManager();
        
        // laadt de sessie
        $session = $request->getSession();
        
        // zoek het mandje
        if (!$session->get('mandje')) {
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        } else {
            $mandje = $session->get('mandje');
        }
        
        // - - - bewerk de verpakkinglijn
        
        foreach ($mandje->getBestellijn() as $mandjelijn) {
            // we overlopen elke bestellijn in het mandje
            if ($mandjelijn->getWijn()->getSlug() == $slug) {
                // laadt de verpakking die bij de verpakking-id hoort
                $dezeVerpakking = $em->getRepository('vinoPillarBundle:Verpakking')->findOneById($verpakking);
                // dit is de juiste bestellijn
                foreach($mandjelijn->getVerpakkinglijn() as $verpakkinglijn) {
                    // we overlopen elke verpakkinglijn in de bestellijn
                    if ($verpakkinglijn->getVerpakking()->getId() === $dezeVerpakking->getId()) {
                        // we moeten vergelijken op VERPAKKING-ID, en niet op de entity VERPAKKING zelf, want
                        // om de een of andere reden lukt dat niet...
                        // ---
                        // de verpakkinglijn bestaat, en dit is ze
                        $nuAantal = $verpakkinglijn->getAantal();
                        $nuAantal -= 1;
                        if ($nuAantal <= 0) {
                            $mandjelijn->removeVerpakkinglijn($verpakkinglijn);
                        } else {
                            $verpakkinglijn->setAantal($nuAantal);
                        }
                    }
                }
            }
        }
        
        $session->set('mandje', $mandje);
        
        // maak flashmessage
        $infoMsg = 'Verpakking uit mandje verwijderd!';
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
        // laadt mandje via redirect
        //return $this->redirect($this->generateUrl('vino_pillar_mandje'));
        // laadt dezelfde pagina via een reload/redirect
        $url = $this->getRequest()->headers->get("referer");
        return $this->redirect($url);
    }
    
    /* * * * * CALLABLES * * * * */
    
    /* * * VERPAKKINGLIJST : returnt een overzicht van alle mogelijke verpakkingen * * */
    
    public function verpakkingLijst() {
        // laadt Doctrine manager
        $em = $this->getDoctrine()->getManager();
        
        // zoek alle producten
        $verpakkinglijst = $em->getRepository('vinoPillarBundle:Verpakking')->findAllSortedByFlessen();
        
        if (!$verpakkinglijst) {
            // toont een foutmelding...
            throw $this->createNotFoundException('Er zijn geen producten in de database...');
        }
        
        return $verpakkinglijst;
    }

}