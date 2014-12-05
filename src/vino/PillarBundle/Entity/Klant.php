<?php
// src/vino/PillarBundle/Entity/Klant.php

namespace vino\PillarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;    // nodig om relatiemappings te kunnen maken
use Symfony\Component\Security\Core\User\UserInterface; // nodig om gebruikers te authoriseren

/**
 * @ORM\Entity
 * @ORM\Table(name="klant")
 */

class Klant implements UserInterface, \Serializable {
    
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
     * @ORM\Column(type="string", length=5, nullable=true)
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
    
    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    protected $isActive;
    
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
        $this->isActive = true;
        // may not be needed, see section on salt
        // $this->salt = md5(uniqid(null, true));
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
     * @inheritDoc
     */
    public function getUsername()
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
     * @inheritDoc
     */
    public function getPassword()
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
     * Set isActive
     * 
     * @param boolean $isActive
     * @return klant
     */
    public function setIsActive($isActive) {
        $this->isActive = $isActive;
        
        return $this;
    }
    
    /**
     * Get isActive
     * 
     * @return boolean
     */
    public function getIsActive() {
        return $this->isActive;
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
    
    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }
    
    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return array('ROLE_KLANT'); // hiermee definieer je de rol die deze entity moet opnemen in de security
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            //$this->vNaam,
            //$this->aNaam,
            //$this->straat,
            //$this->huisnr,
            //$this->busnr,
            //$this->postcode,
            //$this->gemeente,
            $this->email,
            $this->paswoord,
            // see section on salt below
            // $this->salt,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->paswoord,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }
}
