<?php
// src/vino/PillarBundle/Entity/Review.php

namespace vino\PillarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="vino\PillarBundle\Entity\Repository\ReviewRepository")
 * @ORM\Table(name="review")
 * @ORM\HasLifecycleCallbacks
 */

class Review {
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Klant", inversedBy="review")
     * @ORM\JoinColumn(name="klant_id", referencedColumnName="id")
     */
    protected $klant;
    
    /**
     * @ORM\ManyToOne(targetEntity="Wijn", inversedBy="review")
     * @ORM\JoinColumn(name="wijn_id", referencedColumnName="id")
     */
    protected $wijn;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $datum;
    
    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $titel;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $tekst;
    
    /**
     * @ORM\Column(type="smallint") 
     */
    protected $rating;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set datum
     *
     * @param \DateTime $datum
     * @return Review
     */
    public function setDatum($datum)
    {
        $this->datum = $datum;

        return $this;
    }

    /**
     * Get datum
     *
     * @return \DateTime 
     */
    public function getDatum()
    {
        return $this->datum;
    }

    /**
     * Set titel
     *
     * @param string $titel
     * @return Review
     */
    public function setTitel($titel)
    {
        $this->titel = $titel;

        return $this;
    }

    /**
     * Get titel
     *
     * @return string 
     */
    public function getTitel()
    {
        return $this->titel;
    }

    /**
     * Set tekst
     *
     * @param string $tekst
     * @return Review
     */
    public function setTekst($tekst)
    {
        $this->tekst = $tekst;

        return $this;
    }

    /**
     * Get tekst
     *
     * @return string 
     */
    public function getTekst()
    {
        return $this->tekst;
    }

    /**
     * Set rating
     *
     * @param integer $rating
     * @return Review
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Get rating
     *
     * @return integer 
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Set klant
     *
     * @param \vino\PillarBundle\Entity\Klant $klant
     * @return Review
     */
    public function setKlant(\vino\PillarBundle\Entity\Klant $klant = null)
    {
        $this->klant = $klant;

        return $this;
    }

    /**
     * Get klant
     *
     * @return \vino\PillarBundle\Entity\Klant 
     */
    public function getKlant()
    {
        return $this->klant;
    }

    /**
     * Set wijn
     *
     * @param \vino\PillarBundle\Entity\Wijn $wijn
     * @return Review
     */
    public function setWijn(\vino\PillarBundle\Entity\Wijn $wijn = null)
    {
        $this->wijn = $wijn;

        return $this;
    }

    /**
     * Get wijn
     *
     * @return \vino\PillarBundle\Entity\Wijn 
     */
    public function getWijn()
    {
        return $this->wijn;
    }
}
