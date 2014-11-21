<?php
// src/vino/PillarBundle/Entity/Categorie.php

namespace vino\PillarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;    // nodig om relatiemappings te kunnen maken

/**
 * @ORM\Entity
 * @ORM\Table(name="categorie")
 */

class Categorie {
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=30)
     */
    protected $naam;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $omschrijving;
    
    /**
     * @ORM\OneToMany(targetEntity="Wijn", mappedBy="categorie")
     */
    protected $wijn;    // deze is nodig om aan te geven dat er many "wijnen" bij one "categorie" horen
    
    public function __construct() {
        $this->wijn = new ArrayCollection();
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
     * Set naam
     *
     * @param string $naam
     * @return Categorie
     */
    public function setNaam($naam)
    {
        $this->naam = $naam;

        return $this;
    }

    /**
     * Get naam
     *
     * @return string 
     */
    public function getNaam()
    {
        return $this->naam;
    }

    /**
     * Set omschrijving
     *
     * @param string $omschrijving
     * @return Categorie
     */
    public function setOmschrijving($omschrijving)
    {
        $this->omschrijving = $omschrijving;

        return $this;
    }

    /**
     * Get omschrijving
     *
     * @return string 
     */
    public function getOmschrijving()
    {
        return $this->omschrijving;
    }

    /**
     * Add wijn
     *
     * @param \vino\PillarBundle\Entity\Wijn $wijn
     * @return Categorie
     */
    public function addWijn(\vino\PillarBundle\Entity\Wijn $wijn)
    {
        $this->wijn[] = $wijn;

        return $this;
    }

    /**
     * Remove wijn
     *
     * @param \vino\PillarBundle\Entity\Wijn $wijn
     */
    public function removeWijn(\vino\PillarBundle\Entity\Wijn $wijn)
    {
        $this->wijn->removeElement($wijn);
    }

    /**
     * Get wijn
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWijn()
    {
        return $this->wijn;
    }
}
