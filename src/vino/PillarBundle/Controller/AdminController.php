<?php
// src/vino/PillarBundle/Controller/AdminController.php

namespace vino\PillarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use vino\PillarBundle\Entity\Categorie;
use vino\PillarBundle\Entity\Wijn;
use vino\PillarBundle\Entity\Land;

use vino\PillarBundle\Form\Type\WijnType;
use vino\PillarBundle\Form\Type\CategorieType;
use vino\PillarBundle\Form\Type\LandType;

use Cocur\Slugify\Slugify;

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
    
    /*
     * * * * * WIJN-FUNCTIES * * * * *
     */
    
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
        
        if (!$productlijst) {
            $this->get('session')->getFlashBag()->add('msg_warning', 'Er zijn geen wijnen in de database - <a href="' . $this->generateUrl('vino_pillar_nieuwewijn'). '">voeg een nieuwe wijn toe</a>.');
            $this->redirect($this->generateUrl('vino_pillar_nieuwewijn'));
        }
        
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
            //throw $this->createNotFoundException('Sorry! Dit item konden we niet terugvinden...');
            // maar we kunnen alternatief ook een redirect doen naar een andere pagina
            $this->get('session')->getFlashBag()->add('msg_error', 'Sorry! Dit item kon niet worden teruggevonden.');
            return $this->redirect($this->generateUrl('vino_pillar_allewijnen'));
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
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_error', 'Review bestaat niet - verwijderen kon niet worden uitgevoerd.');
        } else {
            // review bestaat wel
            $em->remove($review);
            $em->flush();
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_success', 'Review succesvol verwijderd.');
        }
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
        // maak een form op basis van het form-type WijnType
        $form = $this->createForm(new WijnType(), $wijn, array('attr' => array('class' => 'form-horizontal')));
        
        $form->handleRequest($request);

        // indien sprake is van een ingediend form, doe dit
        if ($form->isValid()) {
            // voer acties uit voor een valide form: in database plaatsen
            $slugify = new Slugify();
            $preSlug = $form['naam']->getData() . "-" . $form['jaar']->getData();
            $slug = $slugify->slugify($preSlug);
            //$slug = $this->slugThis($form['naam']->getData()) . '-' . $this->slugThis($form['jaar']->getData());
            //$slug = $this->slugThis($form['naam']->getData());
            $wijn->setSlug($slug);
            $em->persist($wijn);
            $em->flush();
            
            // DEBUG voor de values
            //$deNaam = $form['naam']->getData();
            //$hetJaar = $form['jaar']->getData();
            //$deGemaakteSlug = $slug;
            
            /*return $this->render('vinoPillarBundle:Default:test.html.twig', array(
                'user' => $user,
                'mandje' => $mandje,
                'denaam' => $deNaam,
                'hetjaar' => $hetJaar,
                'deslug' => $deGemaakteSlug,    // DEBUG
                //'deslug' => $str, // DEBUG
            ));*/

            // geef de melding weer en redirect
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_success', 'Wijn succesvol toegevoegd!');
            return $this->redirect($this->generateUrl('vino_pillar_allewijnen'));
        }
        $form_view = $form->createView();
        
        return $this->render('vinoPillarBundle:Admin:nieuwewijn.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
            'form' => $form_view,
                ));
    }
    
    /* * * BEWERK WIJN : laadt een wijn uit de database opdat deze bewerkt kan worden * * */
    
    public function bewerkWijnAction(Request $request, $id) {
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt de user
        $user = $this->getUser();
        
        $mandje = $session->get('mandje');
        
        // laadt de manager
        $em = $this->getDoctrine()->getManager();
        
        // roep de wijn uit de database
        if (!$oudeWijn = $em->getRepository('vinoPillarBundle:Wijn')->findOneById($id)) {
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_error', 'Wijn bestaat niet - bewerken kon niet worden uitgevoerd.');
            return $this->redirect($this->generateUrl('vino_pillar_allewijnen'));
        }
        // maak het form
        
        $form = $this->createForm(new WijnType(), $oudeWijn, array('attr' => array('class' => 'form-horizontal')));
        
        $form->handleRequest($request);

        // indien sprake is van een ingediend form, doe dit
        if ($form->isValid()) {
            // voer acties uit voor een valide form: in database plaatsen
            $slugify = new Slugify();
            $preSlug = $form['naam']->getData() . "-" . $form['jaar']->getData();
            $slug = $slugify->slugify($preSlug);
            $oudeWijn->setSlug($slug);
            $em->persist($oudeWijn);
            $em->flush();
            
            // geef de melding weer en redirect
            $this->get('session')->getFlashBag()->add('msg_succes', 'Wijn succesvol bewerkt!');
            return $this->redirect($this->generateUrl('vino_pillar_allewijnen'));
        }
        
        $form_view = $form->createView();
        
        return $this->render('vinoPillarBundle:Admin:bewerkwijn.html.twig', array(
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
            $this->get('session')->getFlashBag()->add('msg_error', $infoMsg);
        } else {
            // bestelling bestaat wel
            $em->remove($wijn);
            $em->flush();
            $infoMsg = 'Wijn succesvol verwijderd.';
            $this->get('session')->getFlashBag()->add('msg_success', $infoMsg);
        }
        
        return $this->redirect($this->generateUrl('vino_pillar_allewijnen'));
    }
    
    /*
     * * * * * CATEGORIE-FUNCTIES * * * * *
     */
    
    /* * * ALLE CATEGORIEËN : geef een overzicht van alle categorieën * * */
    
    public function alleCatsAction(Request $request) {
        // laadt de sessie
        $session = $request->getSession();
        // laadt de user en mandje
        $user = $this->getUser();
        $mandje = $session->get('mandje');
        // laadt de manager en lijst
        $em = $this->getDoctrine()->getManager();
        $alleCats = $em->getRepository('vinoPillarBundle:Categorie')->findAllSortedByNaam();
        
        if (!$alleCats) {
            // er zijn geen categorieën
            $this->get('session')->getFlashBag()->add('msg_warning', 'Er zijn nog geen categorieën in de database - <a href="' . $this->generateUrl('vino_pillar_nieuwecat'). '">voeg een nieuwe categorie toe</a>.');
            $this->redirect($this->generateUrl('vino_pillar_nieuwecat'));
        }
        
        return $this->render('vinoPillarBundle:Admin:catlijst.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
            'catlijst' => $alleCats,
        ));
    }
    
    public function nieuweCatAction(Request $request) {
        // laadt de sessie
        $session = $request->getSession();
        // laadt de user en mandje
        $user = $this->getUser();
        $mandje = $session->get('mandje');
        // laadt de manager
        $em = $this->getDoctrine()->getManager();
        
        // maak het form
        $categorie = new Categorie(); // roep de entity "categorie" aan
        // maak een form op basis van het form-type CategorieType
        $form = $this->createForm(new CategorieType(), $categorie, array('attr' => array('class' => 'form-horizontal')));
        
        $form->handleRequest($request);

        // indien sprake is van een ingediend form, doe dit
        if ($form->isValid()) {
            // voer acties uit voor een valide form: in database plaatsen
            $em->persist($categorie);
            $em->flush();
            
            // geef de melding weer en redirect
            $infoMsg = 'Categorie succesvol toegevoegd!';
            $this->get('session')->getFlashBag()->add('msg_success', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_allecats'));
        }
        $form_view = $form->createView();
        
        return $this->render('vinoPillarBundle:Admin:nieuwecat.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
            'form' => $form_view,
                ));
    }
    
    public function bewerkCatAction(Request $request, $id) {
        // laadt de sessie
        $session = $request->getSession();
        // laadt de user en mandje
        $user = $this->getUser();
        $mandje = $session->get('mandje');
        // laadt de manager
        $em = $this->getDoctrine()->getManager();
        
        // roep de categorie uit de database
        if (!$oudeCategorie = $em->getRepository('vinoPillarBundle:Categorie')->findOneById($id)) {
            $infoMsg = 'Categorie bestaat niet - bewerken kon niet worden uitgevoerd.';
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_error', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_allecats'));
        }
        
        // maak een form op basis van het form-type CategorieType
        $form = $this->createForm(new CategorieType(), $oudeCategorie, array('attr' => array('class' => 'form-horizontal')));
        
        $form->handleRequest($request);

        // indien sprake is van een ingediend form, doe dit
        if ($form->isValid()) {
            // voer acties uit voor een valide form: in database plaatsen
            $em->persist($oudeCategorie);
            $em->flush();
            
            // geef de melding weer en redirect
            $infoMsg = 'Categorie succesvol aangepast!';
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_success', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_allecats'));
        }
        $form_view = $form->createView();
        
        return $this->render('vinoPillarBundle:Admin:bewerkcat.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
            'form' => $form_view,
                ));
    }
    
    public function verwijderCatAction($id) {
        $em = $this->getDoctrine()->getManager();
        
        // laadt de cat
        if(!$cat = $em->getRepository('vinoPillarBundle:Categorie')->findOneById($id)) {
            // cat bestaat niet
            $infoMsg = 'Categorie bestaat niet - verwijderen kon niet worden uitgevoerd.';
            $this->get('session')->getFlashBag()->add('msg_error', $infoMsg);
        } else {
            // cat bestaat wel
            // checken of cat niet in wijnen zit
            if ($em->getRepository('vinoPillarBundle:Wijn')->findOneByCategorie($cat)) {
                $checkMsg = 'Kan categorie niet verwijderen omdat er wijnen zijn met deze categorie. Verwijder of bewerk eerst de verwante <a href="' . $this->generateUrl('vino_pillar_allewijnen') . '">wijnen</a>.';
                $this->get('session')
                    ->getFlashBag()
                    ->add('msg_error', $checkMsg);
                return $this->redirect($this->generateUrl('vino_pillar_allecats'));
            }
            $em->remove($cat);
            $em->flush();
            $infoMsg = 'Categorie succesvol verwijderd.';
            $this->get('session')->getFlashBag()->add('msg_success', $infoMsg);
        }
        return $this->redirect($this->generateUrl('vino_pillar_allecats'));
    }
    
    /*
     * * * * * LAND-FUNCTIES * * * * *
     */
    
    /* * * ALLE LANDEN : geef een overzicht van alle categorieën * * */
    
    public function alleLandenAction(Request $request) {
        // laadt de sessie
        $session = $request->getSession();
        // laadt de user en mandje
        $user = $this->getUser();
        $mandje = $session->get('mandje');
        // laadt de manager en lijst
        $em = $this->getDoctrine()->getManager();
        $alleLanden = $em->getRepository('vinoPillarBundle:Land')->findAllSortedByNaam();
        
        if (!$alleLanden) {
            $this->get('session')->getFlashBag()->add('msg_warning', 'Er zijn geen landen in de database - <a href="' . $this->generateUrl('vino_pillar_nieuweland'). '">voeg een nieuw land toe</a>.');
            $this->redirect($this->generateUrl('vino_pillar_nieuweland'));
        }
        
        return $this->render('vinoPillarBundle:Admin:landlijst.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
            'catlijst' => $alleLanden,
        ));
    }
    
    public function nieuweLandAction(Request $request) {
        // laadt de sessie
        $session = $request->getSession();
        // laadt de user en mandje
        $user = $this->getUser();
        $mandje = $session->get('mandje');
        // laadt de manager
        $em = $this->getDoctrine()->getManager();
        
        // maak het form
        $land = new Land(); // roep de entity "categorie" aan
        // maak een form op basis van het form-type CategorieType
        $form = $this->createForm(new LandType(), $land, array('attr' => array('class' => 'form-horizontal')));
        
        $form->handleRequest($request);

        // indien sprake is van een ingediend form, doe dit
        if ($form->isValid()) {
            // voer acties uit voor een valide form: in database plaatsen
            $em->persist($land);
            $em->flush();
            
            // geef de melding weer en redirect
            $infoMsg = 'Land succesvol toegevoegd!';
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_success', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_allelanden'));
        }
        $form_view = $form->createView();
        
        return $this->render('vinoPillarBundle:Admin:nieuweland.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
            'form' => $form_view,
                ));
    }
    
    public function bewerkLandAction(Request $request, $id) {
        // laadt de sessie
        $session = $request->getSession();
        // laadt de user en mandje
        $user = $this->getUser();
        $mandje = $session->get('mandje');
        // laadt de manager
        $em = $this->getDoctrine()->getManager();
        
        // roep de categorie uit de database
        if (!$oudeLand = $em->getRepository('vinoPillarBundle:Land')->findOneById($id)) {
            $infoMsg = 'Land bestaat niet - bewerken kon niet worden uitgevoerd.';
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_error', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_allelanden'));
        }
        
        // maak een form op basis van het form-type CategorieType
        $form = $this->createForm(new LandType(), $oudeLand, array('attr' => array('class' => 'form-horizontal')));
        
        $form->handleRequest($request);

        // indien sprake is van een ingediend form, doe dit
        if ($form->isValid()) {
            // voer acties uit voor een valide form: in database plaatsen
            $em->persist($oudeLand);
            $em->flush();
            
            // geef de melding weer en redirect
            $infoMsg = 'Land succesvol aangepast!';
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_success', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_allelanden'));
        }
        $form_view = $form->createView();
        
        return $this->render('vinoPillarBundle:Admin:bewerkland.html.twig', array(
            'user' => $user,
            'mandje' => $mandje,
            'form' => $form_view,
                ));
    }
    
    public function verwijderLandAction($id) {
        $em = $this->getDoctrine()->getManager();
        
        // laadt het land
        if(!$land = $em->getRepository('vinoPillarBundle:Land')->findOneById($id)) {
            // land bestaat niet
            $infoMsg = 'Land bestaat niet - verwijderen kon niet worden uitgevoerd.';
            $this->get('session')->getFlashBag()->add('msg_error', $infoMsg);
        } else {
            // land bestaat wel
            // checken of land niet in wijnen zit
            if ($em->getRepository('vinoPillarBundle:Wijn')->findOneByLand($land)) {
                $checkMsg = 'Kan land niet verwijderen omdat er wijnen zijn met dit land. Verwijder of bewerk eerst de verwante <a href="' . $this->generateUrl('vino_pillar_allewijnen') . '">wijnen</a>.';
                $this->get('session')
                    ->getFlashBag()
                    ->add('msg_warning', $checkMsg);
                return $this->redirect($this->generateUrl('vino_pillar_allelanden'));
            }
            $em->remove($land);
            $em->flush();
            $infoMsg = 'Land succesvol verwijderd.';
            $this->get('session')->getFlashBag()->add('msg_success', $infoMsg);
        }
        return $this->redirect($this->generateUrl('vino_pillar_allelanden'));
    }
    
    /*
     * * * * * BESTELLING-FUNCTIES * * * * *
     */
    
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
        
        if (!$alleBestellingen) {
            $this->get('session')->getFlashBag()->add('msg_warning', 'Er zijn geen bestellingen in de database.');
            $this->redirect($this->generateUrl('vino_pillar_admin'));
        }
        
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
        if(!$teTonenBestelling) {
            $this->get('session')->getFlashBag()->add('msg_error', 'Deze bestelling kon niet worden teruggevonden!');
            return $this->redirect($this->generateUrl('vino_pillar_allebestellingen'));
        }
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
        if(!$teTonenBestelling) {
            // bestelling bestaat niet
            $this->get('session')->getFlashBag()->add('msg_error', 'Deze bestelling kon niet worden teruggevonden!');
        } else {
            // bestelling bestaat wel
            $em->remove($teTonenBestelling);
            $em->flush();
            $infoMsg = 'Bestelling succesvol verwijderd.';
            $this->get('session')
                ->getFlashBag()
                ->add('msg_success', $infoMsg);
        }
        return $this->redirect($this->generateUrl('vino_pillar_allebestellingen'));
    }
    
    /* * * SWITCH BESTELSTATUS : verandert de bestelstatus * * */
    
    public function switchBestelstatusAction($id) {
        $em = $this->getDoctrine()->getManager();
        
        // laadt de bestelling
        $teTonenBestelling = $em->getRepository('vinoPillarBundle:Bestelling')->findOneById($id);
        if(!$teTonenBestelling) {
            // bestelling bestaat niet
            $infoMsg = 'Bestelling bestaat niet - statusupdate kon niet worden uitgevoerd.';
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_error', $infoMsg);
            return $this->redirect($this->generateUrl('vino_pillar_allebestellingen'));
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
                ->add('msg_success', $infoMsg);
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
}