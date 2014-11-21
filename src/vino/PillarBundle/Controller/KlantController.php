<?php
// src/vino/PillarBundle/Controller/KlantController.php

namespace vino\PillarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use vino\PillarBundle\Entity\Klant;
use vino\PillarBundle\Entity\Wijn;
use vino\PillarBundle\Entity\Review;


class KlantController extends Controller {
    
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
                    ->add('infomsg', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        } else {
            KlantController::maakDummyKlant();
        }
        
        // maak flashmessage
        $infoMsg = 'Klant Dummy Dumnuts aangemaakt!';
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
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
                ->add('infomsg', $infoMsg);
        return $this->redirect($this->generateUrl('vino_pillar_wijndetail', array('slug' => $slug,)));
    }
    
}