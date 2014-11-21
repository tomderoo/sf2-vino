<?php
// src/vino/PillarBundle/Controller/ProductController.php

namespace vino\PillarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use vino\PillarBundle\Entity\Wijn;
use vino\PillarBundle\Entity\Categorie;
use vino\PillarBundle\Entity\Land;

class ProductController extends Controller {
    
    public function wijnlijstAction(Request $request) {
        // toont alle producten in de database
        
        // verifieer de user
        $session = $request->getSession();
        
        if (!$session->get('user')) {
            // no user exists
            $user = "anon";
            $session->set('user', $user);
        } else {
            $user = $session->get('user');
        }
        
        // laadt Doctrine manager
        $em = $this->getDoctrine()->getManager();
        
        // zoek alle producten
        $productlijst = $em->getRepository('vinoPillarBundle:Wijn')->findAll();
        
        if (!$productlijst) {
            // toont een foutmelding...
            //throw $this->createNotFoundException('Er zijn geen producten in de database...');
            // maar we kunnen alternatief ook een redirect doen naar een andere pagina
            $this->get('session')
                    ->getFlashBag()
                    ->add('infomsg', 'Er zijn geen wijnen in de database');
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        }
        
        foreach ($productlijst as $product) {
            $dezeRating = ProductController::vindWijnRating($product->getId());
            $product->setRating($dezeRating);
        }
        
        return $this->render('vinoPillarBundle:Product:productlijst.html.twig', array(
            'user' => $user,
            'productlijst' => $productlijst,
        ));
    }
    
    public function vindWijnRating($wijnid) {
        $em = $this->getDoctrine()->getManager();
        
        // zoek alle ratings voor deze wijn
        $reviewlijst = $em->getRepository('vinoPillarBundle:Review')->findByWijn($wijnid);
        
        if(!$reviewlijst) {
            return 0;
        }
        
        $count = 0;
        $ratingTotaal = 0;
        foreach ($reviewlijst as $review) {
            $count++;
            $ratingTotaal += $review->getRating();
        }
        return $ratingTotaal / $count;
    }
    
    public function productdetailAction(Request $request, $slug) {
        // toont een productdetail op basis van een slug
        
        // verifieer de user
        $session = $request->getSession();
        
        if (!$session->get('user')) {
            // no user exists
            $user = "anon";
            $session->set('user', $user);
        } else {
            $user = $session->get('user');
        }
        
        // laadt Doctrine manager
        $em = $this->getDoctrine()->getManager();
        
        // zoek het productdetail in de database op basis van de slug
        $product = $em->getRepository('vinoPillarBundle:Wijn')->findOneBySlug($slug);
        
        // $product = null;
        
        // indien de slug niet kan worden gekoppeld aan de database
        if (!$product) {
            // toont een foutmelding...
            throw $this->createNotFoundException('Sorry! Dit item konden we niet terugvinden...');
            // maar we kunnen alternatief ook een redirect doen naar een andere pagina
            //return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        }
        
        // zoek de bijhorende comments
        $reviews = $em->getRepository('vinoPillarBundle:Review')->findByWijn($product->getId());
        if (!$reviews) {
            $reviews = null;
        }
        //$comments = null;
        
        // toon het productdetail
        return $this->render('vinoPillarBundle:Product:productdetail.html.twig', array(
            'user' => $user,
            'product' => $product,
            'reviews' => $reviews,
        ));
    }
    
    public function makedummyAction() {
        $manager = $this->getDoctrine()->getManager();
        
        // maak een empty infoMsg
        $infoMsg = "";
        
        // maak de slug
        $productSlug = 'beaujolais-1998';
        // test de slug
        $test = $manager->getRepository('vinoPillarBundle:Wijn')->findOneBySlug($productSlug);
        if ($test) {
            $uri = $this->get('router')->generate('vino_pillar_wijndetail', array('slug' => $productSlug,));
            $infoMsg = "De dummywijn <a href=" . $uri . ">bestaat reeds</a>!";
            // maak flashmessage
            $this->get('session')
                    ->getFlashBag()
                    ->add('infomsg', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        }
        
        // zoek de categorie Rood, indien niet bestaat: maak de categorie
        $categorie = $manager->getRepository('vinoPillarBundle:Categorie')->findOneByNaam('Rood');
        if (!$categorie) {
            $categorie = new Categorie();
            $categorie->setNaam('Rood');
            $categorie->setOmschrijving('De kleur van de liefde en nog van die zever');
        }
        
        // zoek het land Frankrijk, indien niet bestaat: maak het land
        $land = $manager->getRepository('vinoPillarBundle:Land')->findOneByNaam('Frankrijk');
        if (!$land) {
            $land = new Land();
            $land->setNaam('Frankrijk');
            $land->setOmschrijving('Ze spreken er Frans, dragen graag modieuze kledij, eten graag kaas en brood, en verdoen heel vaak hun tijd met wat zij het echte leven noemen.');
        }
        
        // maak het product
        $product = new Wijn();
        $product->setNaam('Beaujolais');
        $product->setJaar(1998);
        $product->setOmschrijving('Tis ne willekeurige wijn gast...!');
        $product->setPrijs(820);
        $product->setSlug('beaujolais-1998');
        
        // maak de relaties
        $product->setCategorie($categorie);
        $product->setLand($land);
        
        // persisten en flushen
        $manager->persist($categorie);
        $manager->persist($land);
        $manager->persist($product);
        $manager->flush();
        
        // maak flashmessage
        $infoMsg = 'Wijn met slug ' . $productSlug . ' succesvol aangemaakt!';
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
        return $this->redirect($this->generateUrl('vino_pillar_homepage'));
    }
}