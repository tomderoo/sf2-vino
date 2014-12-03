<?php
// src/vino/PillarBundle/Controller/AdminController.php

namespace vino\PillarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use vino\PillarBundle\Entity\Categorie;
use vino\PillarBundle\Entity\Wijn;
use vino\PillarBundle\Entity\Land;

class AdminController extends Controller {
    
    /* * * ADMIN HOME * * */
    
    public function adminHomeAction(Request $request) {
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt de user
        $user = $this->getUser();
        
        $mandje = $session->get('mandje');
        
        return $this->render('vinoPillarBundle:Admin:adminhome.html.twig', array(
            'user' => $user,
            'mandje' => $mandje
                ));
    }
    
    /* * * ALLE WIJNEN : toont een lijst van alle wijnproducten * * */
    
    public function alleWijnenAction(Request $request) {
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt de user
        $user = $this->getUser();
        
        $mandje = $session->get('mandje');
        
        // laadt de manager en lijst
        $em = $this->getDoctrine()->getManager();
        
        $productlijst = $em->getRepository('vinoPillarBundle:Wijn')->findAll();
        
        foreach ($productlijst as $product) {
            $dezeRating = $this->vindWijnRating($product->getId());
            $product->setRating($dezeRating);
        }
        
        return $this->render('vinoPillarBundle:Admin:productlijst.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
            'productlijst' => $productlijst,
                ));
    }
    
    /* * * ADMIN WIJNDETAIL : toont detailpagina van een bepaalde wijn * * */
    
    public function detailWijnAction(Request $request, $id) {
        // laadt Doctrine manager
        $em = $this->getDoctrine()->getManager();
        
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt het mandje
        $mandje = $session->get('mandje');
        
        // laadt de user
        $user = $this->getUser();
        
        // zoek het productdetail in de database op basis van de slug
        $product = $em->getRepository('vinoPillarBundle:Wijn')->findOneById($id);
        
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
        
        // toon het productdetail
        return $this->render('vinoPillarBundle:Admin:productdetail.html.twig', array(
            'user' => $user,
            'product' => $product,
            'reviews' => $reviews,
            'mandje' => $mandje,
        ));
    }
    
    /* * * VERWIJDER REVIEW : verwijdert een review van een bepaalde wijn * * */
    
    public function verwijderReviewAction($wijnid, $id) {
        $em = $this->getDoctrine()->getManager();
        
        // laadt de review
        $review = $em->getRepository('vinoPillarBundle:Review')->findOneById($id);
        if(!$review) {
            // review bestaat niet
            $infoMsg = 'Review bestaat niet - verwijderen kon niet worden uitgevoerd.';
        } else {
            // review bestaat wel
            $em->remove($review);
            $em->flush();
            $infoMsg = 'Review succesvol verwijderd.';
        }
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
        return $this->redirect($this->generateUrl('vino_pillar_adminwijndetail', array('id' => $wijnid)));
    }
    
    /* * * NIEUWE WIJN : nieuwe wijn toevoegen * * */
    
    public function nieuweWijnAction(Request $request) {
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt de user
        $user = $this->getUser();
        
        $mandje = $session->get('mandje');
        
        // laadt de manager
        $em = $this->getDoctrine()->getManager();
        
        // maak het form
        $wijn = new Wijn(); // roep de entity "wijn" aan
        
        //$categoriekeuze = $em->getRepository('vinoPillarBundle:EntityRepository:Categorie')->findAll();
        
        //$landkeuze = $em->getRepository('vinoPillarBundle:EntityRepository:Land')->findAll();
        
        $form = $this->createFormBuilder($wijn, array('attr' => array('class' => 'form-horizontal')))
                ->add('naam', 'text', array ('attr' => array('class' => 'form-control')))
                ->add('jaar', 'integer', array ('attr' => array('class' => 'form-control')))
                ->add('omschrijving', 'textarea', array ('attr' => array('class' => 'form-control')))
                ->add('categorie', 'entity', array(
                    'attr' => array('class' => 'form-control'),
                    'class' => 'vinoPillarBundle:Categorie',
                    'property' => 'naam',
                    'expanded' => false,
                    'multiple' => false,
                    ))
                ->add('land', 'entity', array(
                    'attr' => array('class' => 'form-control'),
                    'class' => 'vinoPillarBundle:Land',
                    'property' => 'naam',
                    'expanded' => false,
                    'multiple' => false,
                ))
                ->add('prijs', 'integer', array ('attr' => array('class' => 'form-control')))
                ->add('save', 'submit', array('attr' => array('class' => 'btn btn-default'), 'label' => 'Toevoegen'))
                ->getForm();

        $form->handleRequest($request);

        // indien sprake is van een ingediend form, doe dit
        if ($form->isValid()) {
            // voer acties uit voor een valide form: in database plaatsen
            $slug = $this->slugThis($form['naam']->getData()) . '-' . $this->slugThis($form['jaar']->getData());
            $wijn->setSlug($slug);
            $em->persist($wijn);
            $em->flush();

            // geef de melding weer
            $infoMsg = 'Wijn succesvol toegevoegd!';
            $this->get('session')
                    ->getFlashBag()
                    ->add('infomsg', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_allewijnen'));

        }
        $form_view = $form->createView();
        
        return $this->render('vinoPillarBundle:Admin:nieuwewijn.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
            'form' => $form_view,
                ));
    }
    
    /* * * VERWIJDER WIJN : verwijdert wijn met bepaalde id uit database * * */
    
    public function verwijderWijnAction($id) {
        $em = $this->getDoctrine()->getManager();
        
        // laadt de bestelling
        $wijn = $em->getRepository('vinoPillarBundle:Wijn')->findOneById($id);
        if(!$wijn) {
            // wijn bestaat niet
            $infoMsg = 'Wijn bestaat niet - verwijderen kon niet worden uitgevoerd.';
        } else {
            // bestelling bestaat wel
            $em->remove($wijn);
            $em->flush();
            $infoMsg = 'Wijn succesvol verwijderd.';
        }
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
        return $this->redirect($this->generateUrl('vino_pillar_allewijnen'));
    }
    
    /* * * ALLE BESTELLINGEN : toont een lijst van alle bestellingen * * */
    
    public function alleBestellingenAction(Request $request) {
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt de user
        $user = $this->getUser();
        
        $mandje = $session->get('mandje');
        
        // laadt de manager en lijst
        $em = $this->getDoctrine()->getManager();
        
        $alleBestellingen = $em->getRepository('vinoPillarBundle:Bestelling')->findAllSortedById();
        
        return $this->render('vinoPillarBundle:Admin:bestellingen.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
            'bestellingen' => $alleBestellingen,
                ));
    }
    
    /* * * TOON BESTELLING * * */
    
    public function toonBestellingAction($id) {
        $em = $this->getDoctrine()->getManager();
        
        // laadt de bestelling
        $teTonenBestelling = $em->getRepository('vinoPillarBundle:Bestelling')->findOneById($id);
        //if($teTonenBestelling == null) {
        //    return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        //}
        return $this->render('vinoPillarBundle:Admin:bestelling.html.twig', array(
                'user' => $this->getUser(),
                'mandje' => $this->getRequest()->getSession()->get('mandje'),
                'bestelling' => $teTonenBestelling,
            ));
    }
    
    /* * * VERWIJDER BESTELLING * * */
    
    public function verwijderBestellingAction($id) {
        $em = $this->getDoctrine()->getManager();
        
        // laadt de bestelling
        $teTonenBestelling = $em->getRepository('vinoPillarBundle:Bestelling')->findOneById($id);
        if($teTonenBestelling == null) {
            // bestelling bestaat niet
            $infoMsg = 'Bestelling bestaat niet - verwijderen kon niet worden uitgevoerd.';
        } else {
            // bestelling bestaat wel
            $em->remove($teTonenBestelling);
            $em->flush();
            $infoMsg = 'Bestelling succesvol verwijderd.';
        }
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
        return $this->redirect($this->generateUrl('vino_pillar_allebestellingen'));
    }
    
    /* * * SWITCH BESTELSTATUS : verandert de bestelstatus * * */
    
    public function switchBestelstatusAction($id) {
        $em = $this->getDoctrine()->getManager();
        
        // laadt de bestelling
        $teTonenBestelling = $em->getRepository('vinoPillarBundle:Bestelling')->findOneById($id);
        if($teTonenBestelling == null) {
            // bestelling bestaat niet
            $infoMsg = 'Bestelling bestaat niet - statusupdate kon niet worden uitgevoerd.';
            $this->get('session')
                    ->getFlashBag()
                    ->add('infomsg', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        }
        // switch de huidige status
        if($teTonenBestelling->getBestelstatus() == 0) {
            $teTonenBestelling->setBestelstatus(1);
        } else {
            $teTonenBestelling->setBestelstatus(0);
        }
        $em->flush();
        
        $infoMsg = 'Bestelstatus aangepast voor bestelling met id ' . $id;
        $this->get('session')
                ->getFlashBag()
                ->add('infomsg', $infoMsg);
        return $this->redirect($this->generateUrl('vino_pillar_toonbestelling', array('id' => $id)));
    }
    
    /* * * * * CALLABLE FUNCTIES * * * * */
    
    /* * * WIJNRATING : vind de gecombineerde rating voor een gegeven wijn * * */

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
    
    public function slugThis($str) {
        // Lower case the string and remove whitespace from the beginning or end
       $str = trim(strtolower($str));

       // Remove single quotes from the string
       $str = str_replace("'", '', $str);
       
       // vervang accentkarakters door normale equivalenten
       /*$matchArray = array(
           "á" => "a",
           "â" => "a",
           "ô" => "o",
       );
       
       foreach($matchArray as $accented => $normal) {
           $str = str_replace($accented, $normal, $str);
       }
       */
       $str = preg_replace("[ô]", "o", $str);
       //$str = preg_replace("/&([a-z])[a-z]+;/i", "$1",$str);
       
       // Every character other than a-z, 0-9 will be replaced with a single dash (-)
       $str = preg_replace("/[^a-z0-9]+/", "-", $str);

       // Remove any beginning or trailing dashes
       $str = trim($str, "-");

       return $str;
    }
}