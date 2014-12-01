<?php
// src/vino/PillarBundle/Entity/Verpakking.php

namespace vino\PillarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;    // nodig om relatiemappings te kunnen maken

/**
 * @ORM\Entity(repositoryClass="vino\PillarBundle\Entity\Repository\VerpakkingRepository")
 * @ORM\Table(name="verpakking")
 */

class Verpakking {
    
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
     * @ORM\Column(type="smallint")
     */
    protected $aantalFlessen;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $prijs;

    /**
     * @ORM\OneToMany(targetEntity="Verpakkinglijn", mappedBy="verpakking")
     */
    protected $verpakkinglijn;    // deze is nodig om aan te geven dat er many "verpakkinglijn" bij one "verpakking" horen
    
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
     * Set naam
     *
     * @param string $naam
     * @return Verpakking
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
     * Set aantalFlessen
     *
     * @param integer $aantalFlessen
     * @return Verpakking
     */
    public function setAantalFlessen($aantalFlessen)
    {
        $this->aantalFlessen = $aantalFlessen;

        return $this;
    }

    /**
     * Get aantalFlessen
     *
     * @return integer 
     */
    public function getAantalFlessen()
    {
        return $this->aantalFlessen;
    }

    /**
     * Set prijs
     *
     * @param integer $prijs
     * @return Verpakking
     */
    public function setPrijs($prijs)
    {
        $this->prijs = $prijs;

        return $this;
    }

    /**
     * Get prijs
     *
     * @return integer 
     */
    public function getPrijs()
    {
        return $this->prijs;
    }

    /**
     * Add verpakkinglijn
     *
     * @param \vino\PillarBundle\Entity\Verpakkinglijn $verpakkinglijn
     * @return Verpakking
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
