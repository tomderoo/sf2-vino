<?php
// src/vino/PillarBundle/Controller/MandjeController.php

namespace vino\PillarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use vino\PillarBundle\Entity\Bestelling;
use vino\PillarBundle\Entity\Verpakkinglijn;
use vino\PillarBundle\Entity\Bestellijn;
//use \stdClass;

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
        $this->get('session')
                ->getFlashBag()
                ->add('msg_info', $infoMsg);
        
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
            $infoMsg = "De door u gekozen wijn kon niet worden teruggevonden in de database en niet worden toegevoegd aan uw mandje!";
            // maak flashmessage
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_error', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_wijnen'));
        }
        
        // - - - voeg het product toe aan mandje
        
        // verifieer het mandje
        $session = $request->getSession();
        
        if (!$session->get('mandje')) {
            // er is nog geen mandje
            $mandje = new Bestelling();
            $mandje->setLeverwijze(0);
            $mandje->setBestelstatus(0);
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
        $this->get('session')
                ->getFlashBag()
                ->add('msg_success', $infoMsg);
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
        
        $errormade = true;
        // doorlopen van de bestellijnen op zoek naar de slug
        // gebruik van een foreach-lus om "missing indexes" te vermijden (anders moet de array telkens
        // gereset worden OF moet de ->removeBestellijn-functie worden herschreven in de entity)
        foreach($bestellijnArray as $lijnID => $dezeLijn) {
            if ($dezeLijn->getWijn()->getSlug() == $slug) {
                $nuAantal = $dezeLijn->getAantal();
                $nieuwAantal = $nuAantal + $aantal;
                if ($nieuwAantal <= 0) {
                    $mandje->removeBestellijn($bestellijnArray[$lijnID]);
                } else {
                    $bestellijnArray[$lijnID]->setAantal($nieuwAantal);
                }
                $errormade = false;
            }
        }
        if ($errormade) {
            $this->get('session')->getFlashBag()->add('msg_error', 'Deze opdracht kon niet worden uitgevoerd.');
            return $this->redirect($this->generateUrl('vino_pillar_mandje'));
        }
        
        // mandje terugzetten
        $session->set('mandje', $mandje);
        
        // maak flashmessage
        $infoMsg = 'Mandje aangepast!';
        $this->get('session')
                ->getFlashBag()
                ->add('msg_success', $infoMsg);
        
        return $this->redirect($this->generateUrl('vino_pillar_mandje'));
    }
    
    /* * * UIT BESTELLING: verwijdert een mandjelijn uit het mandje * * */
    
    public function uitBestellingAction(Request $request, $slug) {
        // verkrijg het mandje
        $session = $request->getSession();
        
        $mandje = $session->get('mandje');
        
        // zoek de bestellijn
        $bestellijnArray = $mandje->getBestellijn();
        
        if (!$bestellijnArray) {
            $this->get('session')->getFlashBag()->add('msg_error', 'Deze opdracht kon niet worden uitgevoerd.');
            return $this->redirect($this->generateUrl('vino_pillar_mandje'));
        }
        
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
                ->add('msg_success', $infoMsg);
        
        return $this->redirect($this->generateUrl('vino_pillar_mandje'));
    }
    
    /* * * CHECKOUT: eerste fase voor het bevestigen van de bestelling * * */
    /* In dit onderdeel moet worden geregeld:
     * - checkout-aanroep
     * - mogelijkheid om verpakking te kiezen
     * - mogelijkheid om verzending te kiezen
     */
    
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
        
        // sessie klaarmaken voor confirm via flash
        // maak flashmessage
        $flashCheck = 'valar morghulis';
        $this->get('session')
                ->getFlashBag()
                ->add('flashCheck', $flashCheck);
        //$this->get('session')->getFlashBag()->add('infomsg', 'Er is een flashCheck meegegeven');
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
                if (!$dezeVerpakking) {
                    // dit verpakkingstype bestaat niet, dus foute aanroep
                    $this->get('session')->getFlashBag()->add('msg_error', 'Foute aanroep.');
                    return $this->redirect($this->generateUrl('vino_pillar_mandje'));
                }
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
                ->add('msg_success', $infoMsg);
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
                if (!$dezeVerpakking) {
                    // dit verpakkingstype bestaat niet, dus foute aanroep
                    $this->get('session')->getFlashBag()->add('msg_error', 'Foute aanroep.');
                    return $this->redirect($this->generateUrl('vino_pillar_mandje'));
                }
                // dit is de juiste bestellijn
                foreach($mandjelijn->getVerpakkinglijn() as $verpakkinglijn) {
                    // we overlopen elke verpakkinglijn in de bestellijn
                    if ($verpakkinglijn->getVerpakking()->getId() === $dezeVerpakking->getId()) {
                        // we moeten vergelijken op VERPAKKING-ID, en niet op de entity VERPAKKING zelf
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
                ->add('msg_success', $infoMsg);
        // laadt mandje via redirect
        //return $this->redirect($this->generateUrl('vino_pillar_mandje'));
        // laadt dezelfde pagina via een reload/redirect
        $url = $this->getRequest()->headers->get("referer");
        return $this->redirect($url);
    }
    
    /* * * LEVERWIJZE : alterneren in keuzes van leverwijze * * */
    /* - value 0 : levering afhalen
     * - value 1 : levering aan huis
     */
    public function switchLeverwijzeAction(Request $request) {
        if (!$this->getUser() or !$request->getSession()->get('mandje')) {
            $this->get('session')->getFlashBag()->add('msg_error', 'Foute aanroep.');
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        }
        $session = $request->getSession();
        $mandje = $session->get('mandje');
        $leverwijze = $mandje->getLeverwijze();
        if ($leverwijze == 0) {
            $mandje->setLeverwijze(1);
            $infoMsg = 'U hebt gekozen voor levering aan huis.';
        } else {
            $mandje->setLeverwijze(0);
            $infoMsg = 'U hebt gekozen voor afhaling in het magazijn; we zetten de bestelling voor u klaar.';
        }
        $session->set('mandje', $mandje);
        $this->get('session')->getFlashBag()->add('msg_info', $infoMsg);
        //$url = $this->getRequest()->headers->get("referer");
        //return $this->redirect($url);
        return $this->redirect($this->generateUrl('vino_pillar_checkout'));
    }
    
    /* * * CONFIRM : bevestigen van bestelling, inschrijving in de database * * */
    
    public function confirmAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        // laadt de sessie
        $session = $request->getSession();
        
        // check het mandje
        if (!$session->get('mandje')) {
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        } else {
            $mandje = $session->get('mandje');
        }
        
        // checken of er een flashbericht is vanuit checkout; indien niet, redirect
        if (!$this->get('session')->getFlashbag()->get('flashCheck')) {
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        }
        
        //$tempBestelling = clone($mandje);
        
        // alles is in orde, zet de bestelling in de database, ledig het mandje, en geef een confirmatiebericht
        // omwille van "cascade: persist"-issues is het onmogelijk om simpelweg het mandje (dat een entity
        // van "bestelling" is met alles er op en er aan, in principe...) rechtstreeks naar de database te
        // persisten en flushen. Het is noodzakelijk om een geheel nieuw mandje te vullen en de daartoe
        // benodigde entities opnieuw op te vragen uit de database om ze er in te kunnen steken... Daartoe
        // moeten ook de aparte en benodigde entities gepersist en geflusht worden in volgorde dat ze nodig
        // zijn in verdere aanroepingen... Dus eerst de bestelling (omdat die later gerefereerd wordt in
        // bestellijnen), dan de bestellijnen (omdat die later gerefereerd worden in verpakkinglijnen), dan
        // de verpakkinglijnen... Ik zie niet in hoe dit een winst is tov oude rechtstreekse MySQL-aanroepingen.
        // NOOT: het is blijkbaar niet nodig om apart te flushen, enkel om apart te persisten
        // 
        // maak een geheel nieuwe bestelling en vul het met de informatie uit het mandje
        // STAP 1 : maak de bestelling
        $nieuweBestelling = new Bestelling();
        $user = $this->getUser();
        $klant = $em->getRepository('vinoPillarBundle:Klant')->findOneById($user->getId());
        $nieuweBestelling->setKlant($klant);
        //$tempBestelling->setKlant($klant);
        $nieuweBestelling->setDatum(new \DateTime());
        $nieuweBestelling->setLeverwijze($mandje->getLeverwijze());
        $nieuweBestelling->setBestelstatus(0);
        $em->persist($nieuweBestelling);
        // STAP 2: maak de bestellijnen
        foreach($mandje->getBestellijn() as $bestellijn) {
            $nieuweBestellijn = new Bestellijn();
            $nieuweBestellijn->setAantal($bestellijn->getAantal());
            $deWijn = $em->getRepository('vinoPillarBundle:Wijn')->findOneById($bestellijn->getWijn()->getId());
            $nieuweBestellijn->setWijn($deWijn);
            $nieuweBestellijn->setBestelling($nieuweBestelling);
            $em->persist($nieuweBestellijn);
            // STAP 3 : maak de verpakkinglijn
            foreach($bestellijn->getVerpakkinglijn() as $verpakkinglijn) {
                $nieuweVerpakkinglijn = new Verpakkinglijn();
                $nieuweVerpakkinglijn->setAantal($verpakkinglijn->getAantal());
                $deVerpakking = $em->getRepository('vinoPillarBundle:Verpakking')->findOneById($verpakkinglijn->getVerpakking()->getId());
                $nieuweVerpakkinglijn->setVerpakking($deVerpakking);
                $nieuweVerpakkinglijn->setBestellijn($nieuweBestellijn);
                $em->persist($nieuweVerpakkinglijn);
            }
        }
        $em->flush();
        
        // deze ingevoerde bestelling klaarmaken voor view
        $id = $nieuweBestelling->getId();
        /*
         * Het laden van de teTonenBestelling op basis van de id verloopt onvolledig; de bestellijnen en
         * verpakkingslijnen zijn er NIET bij! (Zelfs niet als het bovenstaande nieuweBestelling-object
         * wordt gekloond!!!) Het lijkt alsof die niet meer bestaan of nog niet bestaan op het moment
         * dat hieronder de aanroep wordt gedaan...
         * Wat werkt allemaal niet;
         * - het object uit de database laden (wordt maar half geladen)
         * - nieuwe manager maken
         * - de bestelling clonen (ook daar wordt enkel de klant en de bestelling getoond, niet de
         * bestellijnen en verpakkingslijnen...)
         * - meerdere flush-aanroepen
         * Wat werkt wel;
         * - Het mandje clonen en dat laten zien
         * - Een redirect naar een nieuwe route waar een nieuw php-proces begint waar dan volgens
         * meegegeven id het object uit de database geladen wordt
         */
        //$teTonenBestelling = $em->getRepository('vinoPillarBundle:Bestelling')->findOneById($id);
        $teTonenBestelling = clone($mandje);
        $teTonenBestelling->setKlant($klant);
        $teTonenBestelling->setDatum($nieuweBestelling->getDatum());
        $teTonenBestelling->setBestelstatus(0);
        if(!$teTonenBestelling) {
            $this->get('session')->getFlashBag()->add('msg_error', 'Deze bestelling kon niet worden teruggevonden!');
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        }
        
        // het mandje ledigen
        $session->set('mandje', null);
        
        // maak flashmessage
        $infoMsg = 'Uw bestelling werd succesvol geplaatst!';
        $this->get('session')
                ->getFlashBag()
                ->add('msg_success', $infoMsg);
        //return $this->redirect($this->generateUrl('vino_pillar_afscheid', array('id' => $id)));
        return $this->render('vinoPillarBundle:Mandje:confirm.html.twig', array(
                'user' => $user,
                'mandje' => null,
                'bestelling' => $teTonenBestelling,
            ));
    }
    
    /* * * CONFIRM STAP 2 : dit toont een nieuwe pagina met de bestelling * * */
    public function afscheidAction($id) {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $teTonenBestelling = $em->getRepository('vinoPillarBundle:Bestelling')->findOneById($id);
        //$teTonenBestelling = $tempBestelling;
        if(!$teTonenBestelling) {
            $this->get('session')->getFlashBag()->add('msg_error', 'Deze bestelling kon niet worden teruggevonden!');
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        }
        return $this->render('vinoPillarBundle:Mandje:confirm.html.twig', array(
                'user' => $user,
                'mandje' => null,
                'bestelling' => $teTonenBestelling,
            ));
    }
    
    /* * * * * CALLABLES * * * * */
    
    /* * * MANDJE_CHECK : check het bestaan van het mandje, anders redirect * * */
    public function mandjeCheck(Request $request) {
        $session = $request->getSession();
        if (!$session->get('mandje')) {
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        } else {
            $mandje = $session->get('mandje');
        }
        return $mandje;
    }
    
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