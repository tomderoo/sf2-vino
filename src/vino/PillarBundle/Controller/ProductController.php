<?php
// src/vino/PillarBundle/Controller/ProductController.php

namespace vino\PillarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use vino\PillarBundle\Entity\Wijn;
use vino\PillarBundle\Entity\Categorie;
use vino\PillarBundle\Entity\Land;
use vino\PillarBundle\Entity\Review;
//use Symfony\Component\Form\AbstractType;          // krijg ik niet aan de praat, en is ook niet nodig
//use Symfony\Component\Form\FormBuilderInterface;  // krijg ik niet aan de praat, en is ook niet nodig

//use vino\PillarBundle\Form\DataTransformer\KlantToIdTransformer;

class ProductController extends Controller {
    
    /* * * * * ACTIES * * * * */
    
    /* * * WIJNLIJST : toon alle wijnen * * */
    
    public function wijnlijstAction(Request $request) {
        // toont alle producten in de database
        
        // laadt Doctrine manager
        $em = $this->getDoctrine()->getManager();
        
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt het mandje
        $mandje = $session->get('mandje');
        
        // laadt de user
        //$user = \vino\PillarBundle\Functions\ServiceCollection::checkKlant($session, $em);
        $user = $this->getUser();
        
        // zoek alle producten
        $productlijst = $em->getRepository('vinoPillarBundle:Wijn')->findAll();
        
        if (!$productlijst) {
            // toont een foutmelding...
            //throw $this->createNotFoundException('Er zijn geen producten in de database...');
            // maar we kunnen alternatief ook een redirect doen naar een andere pagina
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_warning', 'Er zijn geen wijnen in de database...');
            return $this->redirect($this->generateUrl('vino_pillar_homepage'));
        }
        
        foreach ($productlijst as $product) {
            $dezeRating = ProductController::vindWijnRating($product->getId());
            $product->setRating($dezeRating);
        }
        
        return $this->render('vinoPillarBundle:Product:productlijst.html.twig', array(
            'user' => $user,
            'productlijst' => $productlijst,
            'mandje' => $mandje,
        ));
    }
    
    /* * * PRODUCTDETAIL : toon één gegeven product in detail * * */
    
    public function productdetailAction(Request $request, $slug) {
        // toont een productdetail op basis van een slug
        
        // laadt Doctrine manager
        $em = $this->getDoctrine()->getManager();
        
        // laadt de sessie
        $session = $request->getSession();
        
        // laadt het mandje
        $mandje = $session->get('mandje');
        
        // laadt de user
        //$user = \vino\PillarBundle\Functions\ServiceCollection::checkKlant($session, $em);
        $user = $this->getUser();
        
        // zoek het productdetail in de database op basis van de slug
        $product = $em->getRepository('vinoPillarBundle:Wijn')->findOneBySlug($slug);
        
        // $product = null;
        
        // indien de slug niet kan worden gekoppeld aan de database
        if (!$product) {
            // toont een foutmelding...
            //throw $this->createNotFoundException('Sorry! Dit item konden we niet terugvinden...');
            // maar we kunnen alternatief ook een redirect doen naar een andere pagina
            $this->get('session')
                    ->getFlashBag()
                    ->add('msg_warning', 'Sorry! Deze wijn konden we niet terugvinden in de database...');
            return $this->redirect($this->generateUrl('vino_pillar_wijnen'));
        }
        
        // laat form zien INDIEN:
        // - gebruiker is ingelogd
        // - gebruiker niet ROLE_ADMIN heeft
        if ($user && !in_array("ROLE_ADMIN", $user->getRoles())) {
            $review = new Review();
            // importeren van "hidden" gegevens eigen aan de klant en het product
            $review->setKlant($user);
            $review->setWijn($product); // deze functie slaat enkel de entity op, zonder de sub-entities - why?
            $review->setDatum(new \DateTime('today'));
            
            $formvisible = 1;   // zet de zichtbaarheid van het form
            
            // checken of deze klant reeds een review heeft ingevoerd
            if ($em
                    ->getRepository('vinoPillarBundle:Review')
                    ->findOneBy(array( 'klant' => $user->getId(), 'wijn' => $product->getId()))
                    ) {
                $formvisible = 0;
            }
            
            $form = $this->createFormBuilder($review)
                    ->add('titel', 'text', array ('attr' => array('class' => 'form-control')))
                    ->add('tekst', 'textarea', array ('attr' => array('class' => 'form-control')))
                    ->add('rating', 'choice', array(
                        'choices' => array(
                            1 => '1 / 5 (slecht) ',
                            2 => '2 / 5 (valt tegen) ',
                            3 => '3 / 5 (gemiddeld) ',
                            4 => '4 / 5 (goed) ',
                            5 => '5 / 5 (fantastisch) '),
                        'expanded' => true,
                        'multiple' => false,
                        //'attr' => array ( 'class' => 'radio'),
                        ))
                    ->add('save', 'submit', array('label' => 'Verstuur review'))
                    ->getForm();
            
            $form->handleRequest($request);
            
            // indien sprake is van een ingediend form, doe dit
            if ($form->isValid()) {
                // voer acties uit voor een valide form: in database plaatsen
                $em->persist($review);
                $em->flush();
                
                // geef de melding weer
                $infoMsg = 'Review succesvol toegevoegd!';
                $this->get('session')
                        ->getFlashBag()
                        ->add('msg_success', $infoMsg);
                
                // disable de form (om geen dubbelposting toe te laten)
                $formvisible = 0;
            }
            $form_view = $form->createView();
        } else {
            $form_view = null;
            $formvisible = 0;
            //echo($user->getUsername());
            //var_dump($user->getRoles());
        }
        
        // zoek de bijhorende rating
        $dezeRating = ProductController::vindWijnRating($product->getId());
        $product->setRating($dezeRating);
        
        // zoek de bijhorende comments
        $reviews = $em->getRepository('vinoPillarBundle:Review')->findByWijn($product->getId());
        if (!$reviews) { $reviews = null; }
        
        // toon het productdetail
        return $this->render('vinoPillarBundle:Product:productdetail.html.twig', array(
            'user' => $user,
            'product' => $product,
            'reviews' => $reviews,
            'form' => $form_view,
            'formvisible' => $formvisible,
            'mandje' => $mandje,
        ));
    }
    
    public function makedummyAction() {
        $manager = $this->getDoctrine()->getManager();
        
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
                    ->add('msg_info', $infoMsg);
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
                ->add('msg_info', $infoMsg);
        return $this->redirect($this->generateUrl('vino_pillar_homepage'));
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