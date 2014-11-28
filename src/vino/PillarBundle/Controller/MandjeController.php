<?php
// src/vino/PillarBundle/Controller/MandjeController.php

namespace vino\PillarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use vino\PillarBundle\Entity\Bestelling;
use vino\PillarBundle\Entity\Mandje;
use vino\PillarBundle\Entity\Bestellijn;
use \stdClass;

class MandjeController extends Controller {
    
    
    /* * * * * ACTIES * * * * */
    
    /* * * BEKIJK MANDJE: bekijk het mandje in een aparte pagina * * */
    
    public function bekijkMandjeAction(Request $request) {
        $session = $request->getSession();
        
        $user = $this->getUser();
        
        if(!$session->get('mandje')) {
            // het mandje is leeg
            return $this->render('vinoPillarBundle:Mandje:mandje.html.twig', array(
                'user' => $user,
                'mandje' => null,
            ));
        }
        
        return $this->render('vinoPillarBundle:Mandje:mandje.html.twig', array(
                'user' => $user,
                'mandje' => $session->get('mandje'),
            ));
    }
    
    /* * * LEDIG MANDJE: zegt het zelf * * */
    
    public function ledigMandjeAction(Request $request) {
        $session = $request->getSession();
        
        $user = $this->getUser();
        
        $session->set('mandje', null);
        
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
        return $this->redirect($this->generateUrl('vino_pillar_mandje'));
    }
    
    /* * * UIT BESTELLING: verwijdert een mandjelijn uit het mandje * * */
    
    public function uitBestellingAction(Request $request, $slug) {
        // verkrijg het mandje
        $session = $request->getSession();
        
        $mandje = $session->get('mandje');
        
        // zoek de bestellijn
        $bestellijnArray = $mandje->getBestellijn();
        
        $count = count($bestellijnArray);
        
        if ($count <= 1) {
            return $this->redirect($this->generateUrl('vino_pillar_ledig'));
        }
        
        for ($i = 0; $i < $count; $i++) {
            if ($bestellijnArray[$i]->getWijn()->getSlug() == $slug) {
                unset($bestellijnArray[$i]);
                // NOG ARRAY COUNT RESET BIJDOEN!
                //$mandje->removeBestellijn($bestellijnArray[$i]);
            }
        }
        
        //$mandje->setBestellijn($bestellijnArray); // dit moet blijkbaar niet? Hoe kan dit?
        $session->set('mandje', $mandje);
        
        return $this->redirect($this->generateUrl('vino_pillar_mandje'));
    }
}