<?php
// src/vino/PillarBundle/Entity/Bestellijn.php

namespace vino\PillarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;    // nodig om relatiemappings te kunnen maken

/**
 * @ORM\Entity(repositoryClass="vino\PillarBundle\Entity\Repository\BestellijnRepository")
 * @ORM\Table(name="bestellijn")
 * @ORM\HasLifecycleCallbacks
 */

class Bestellijn {
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Bestelling", inversedBy="bestellijn")
     * @ORM\JoinColumn(name="bestelling_id", referencedColumnName="id")
     */
    protected $bestelling;
    
    /**
     * @ORM\ManyToOne(targetEntity="Wijn", inversedBy="bestellijn")
     * @ORM\JoinColumn(name="wijn_id", referencedColumnName="id")
     */
    protected $wijn;
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $aantal;
    
    /**
     * @ORM\OneToMany(targetEntity="Verpakkinglijn", mappedBy="bestellijn")
     */
    protected $verpakkinglijn;  // deze is nodig om aan te geven dat er many "verpakkinglijn" bij one "bestellijn" horen
    
    public function __construct() {
        $this->verpakkinglijn = new ArrayCollection();
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
     * Set aantal
     *
     * @param integer $aantal
     * @return Bestellijn
     */
    public function setAantal($aantal)
    {
        $this->aantal = $aantal;

        return $this;
    }

    /**
     * Get aantal
     *
     * @return integer 
     */
    public function getAantal()
    {
        return $this->aantal;
    }

    /**
     * Set bestelling
     *
     * @param \vino\PillarBundle\Entity\Bestelling $bestelling
     * @return Bestellijn
     */
    public function setBestelling(\vino\PillarBundle\Entity\Bestelling $bestelling = null)
    {
        $this->bestelling = $bestelling;

        return $this;
    }

    /**
     * Get bestelling
     *
     * @return \vino\PillarBundle\Entity\Bestelling 
     */
    public function getBestelling()
    {
        return $this->bestelling;
    }

    /**
     * Set wijn
     *
     * @param \vino\PillarBundle\Entity\Wijn $wijn
     * @return Bestellijn
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

    /**
     * Add verpakkinglijn
     *
     * @param \vino\PillarBundle\Entity\Verpakkinglijn $verpakkinglijn
     * @return Bestellijn
     */
    public function addVerpakkinglijn(\vino\PillarBundle\Entity\Verpakkinglijn $verpakkinglijn)
    {
        $this->verpakkinglijn[] = $verpakkinglijn;

        return $this;
    }

    /**
     * Remove verpakkinglijn
     *
     * @param \vino\PillarBundle\Entity\Verpakkinglijn $verpakkinglijn
     */
    public function removeVerpakkinglijn(\vino\PillarBundle\Entity\Verpakkinglijn $verpakkinglijn)
    {
        $this->verpakkinglijn->removeElement($verpakkinglijn);
    }

    /**
     * Get verpakkinglijn
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVerpakkinglijn()
    {
        return $this->verpakkinglijn;
    }
}
