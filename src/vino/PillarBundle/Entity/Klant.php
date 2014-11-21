<?php
// src/vino/PillarBundle/Entity/Klant.php

namespace vino\PillarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;    // nodig om relatiemappings te kunnen maken

/**
 * @ORM\Entity
 * @ORM\Table(name="klant")
 */

class Klant {
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=20)
     */
    protected $vNaam;
    
    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $aNaam;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $straat;
    
    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $huisnr;
    
    /**
     * @ORM\Column(type="string", length=5)
     */
    protected $busnr;
    
    /**
     * @ORM\Column(type="string", length=10)
     */
    protected $postcode;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $gemeente;
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $email;
    
    /**
     * @ORM\Column(type="string", length=200)
     */
    protected $paswoord;
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $level;
    
    // onderstaande zijn de relatiemappings naar tabellen waar "klant" een rol in speelt
    
    /**
     * @ORM\OneToMany(targetEntity="Review", mappedBy="klant")
     */
    protected $review;      // deze is nodig om aan te geven dat er many "review" bij one "klant" horen
    
    /**
     * @ORM\OneToMany(targetEntity="Bestelling", mappedBy="klant")
     */
    protected $bestelling;  // deze is nodig om aan te geven dat er many "bestelling" bij one "klant" horen
    
    public function __construct() {
        $this->review = new ArrayCollection();
        $this->bestelling = new ArrayCollection();
    }


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
     * Set vNaam
     *
     * @param string $vNaam
     * @return Klant
     */
    public function setVNaam($vNaam)
    {
        $this->vNaam = $vNaam;

        return $this;
    }

    /**
     * Get vNaam
     *
     * @return string 
     */
    public function getVNaam()
    {
        return $this->vNaam;
    }

    /**
     * Set aNaam
     *
     * @param string $aNaam
     * @return Klant
     */
    public function setANaam($aNaam)
    {
        $this->aNaam = $aNaam;

        return $this;
    }

    /**
     * Get aNaam
     *
     * @return string 
     */
    public function getANaam()
    {
        return $this->aNaam;
    }

    /**
     * Set straat
     *
     * @param string $straat
     * @return Klant
     */
    public function setStraat($straat)
    {
        $this->straat = $straat;

        return $this;
    }

    /**
     * Get straat
     *
     * @return string 
     */
    public function getStraat()
    {
        return $this->straat;
    }

    /**
     * Set huisnr
     *
     * @param string $huisnr
     * @return Klant
     */
    public function setHuisnr($huisnr)
    {
        $this->huisnr = $huisnr;

        return $this;
    }

    /**
     * Get huisnr
     *
     * @return string 
     */
    public function getHuisnr()
    {
        return $this->huisnr;
    }

    /**
     * Set busnr
     *
     * @param string $busnr
     * @return Klant
     */
    public function setBusnr($busnr)
    {
        $this->busnr = $busnr;

        return $this;
    }

    /**
     * Get busnr
     *
     * @return string 
     */
    public function getBusnr()
    {
        return $this->busnr;
    }

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return Klant
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get postcode
     *
     * @return string 
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set gemeente
     *
     * @param string $gemeente
     * @return Klant
     */
    public function setGemeente($gemeente)
    {
        $this->gemeente = $gemeente;

        return $this;
    }

    /**
     * Get gemeente
     *
     * @return string 
     */
    public function getGemeente()
    {
        return $this->gemeente;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Klant
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set paswoord
     *
     * @param string $paswoord
     * @return Klant
     */
    public function setPaswoord($paswoord)
    {
        $this->paswoord = $paswoord;

        return $this;
    }

    /**
     * Get paswoord
     *
     * @return string 
     */
    public function getPaswoord()
    {
        return $this->paswoord;
    }

    /**
     * Set level
     *
     * @param integer $level
     * @return Klant
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Add review
     *
     * @param \vino\PillarBundle\Entity\Review $review
     * @return Klant
     */
    public function addReview(\vino\PillarBundle\Entity\Review $review)
    {
        $this->review[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param \vino\PillarBundle\Entity\Review $review
     */
    public function removeReview(\vino\PillarBundle\Entity\Review $review)
    {
        $this->review->removeElement($review);
    }

    /**
     * Get review
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReview()
    {
        return $this->review;
    }

    /**
     * Add bestelling
     *
     * @param \vino\PillarBundle\Entity\Bestelling $bestelling
     * @return Klant
     */
    public function addBestelling(\vino\PillarBundle\Entity\Bestelling $bestelling)
    {
        $this->bestelling[] = $bestelling;

        return $this;
    }

    /**
     * Remove bestelling
     *
     * @param \vino\PillarBundle\Entity\Bestelling $bestelling
     */
    public function removeBestelling(\vino\PillarBundle\Entity\Bestelling $bestelling)
    {
        $this->bestelling->removeElement($bestelling);
    }

    /**
     * Get bestelling
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBestelling()
    {
        return $this->bestelling;
    }
}