<?php
// src/vino/PillarBundle/Controller/MandjeController.php

namespace vino\PillarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use vino\PillarBundle\Entity\Wijn;
use vino\PillarBundle\Entity\Mandje;
use vino\PillarBundle\Entity\Klant;
use \stdClass;

class MandjeController extends Controller {
    
    
    /* * * * * ACTIES * * * * */
    
    /* * * INMANDJE: verwerkt het toevoegen van een item aan een mandje * * */
    
    public function inmandjeAction(Request $request, $slug) {
        // voegt een item toe aan een mandje
        
        $manager = $this->getDoctrine()->getManager();
        
        // test de slug
        $product = $manager->getRepository('vinoPillarBundle:Wijn')->findOneBySlug($slug);
        if (!$slug) {
            $infoMsg = "De door u gekozen wijn kon niet worden teruggevonden en niet worden toegevoegd aan uw mandje!";
            // maak flashmessage
            $this->get('session')
                    ->getFlashBag()
                    ->add('infomsg', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_wijn'));
        }
        
        // voeg het product toe aan mandje
        
        // verifieer het mandje
        $session = $request->getSession();
        
        if (!$session->get('mandje')) {
            // er is nog geen mandje
            $mandje = new Mandje();
            $session->set('mandje', $mandje);
        } else {
            $mandje = $session->get('mandje');
        }
        
        // check of het product reeds in het mandje zit
        $bestellingen = $mandje->getBestelling();
        
        $found = false;
        
        for ($i = 0; $i < count($bestellingen); $i++) {
            if($bestellingen[$i]->product_id == $product->getId()) {
                $bestellingen[$i]->aantal++;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $bestellijn = new stdClass();
            $bestellijn->product_id = $product->getId();
            $bestellijn->aantal = 1;
        }

        $mandje->setBestelling($bestellingen);
        
        // maak flashmessage
        $infoMsg = 'Product toegevoegd aan mandje!';
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
        return $this->redirect($this->generateUrl('vino_pillar_wijndetail', array('slug' => $slug,)));
    }
}