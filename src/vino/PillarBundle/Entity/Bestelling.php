<?php
// src/vino/PillarBundle/Entity/Bestelling.php

namespace vino\PillarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;    // nodig om relatiemappings te kunnen maken

/**
 * @ORM\Entity(repositoryClass="vino\PillarBundle\Entity\Repository\BestellingRepository")
 * @ORM\Table(name="bestelling")
 * @ORM\HasLifecycleCallbacks
 */

class Bestelling {
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Klant", inversedBy="bestelling")
     * @ORM\JoinColumn(name="klant_id", referencedColumnName="id")
     */
    protected $klant;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $datum;
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $leverwijze;
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $bestelstatus;
    
    // onderstaande zijn de relatiemappings naar tabellen waar "bestelling" een rol in speelt
    
    /**
     * @ORM\OneToMany(targetEntity="Bestellijn", mappedBy="bestelling")
     */
    protected $bestellijn;      // deze is nodig om aan te geven dat er many "bestellijn" bij one "bestelling" horen
    
    public function __construct() {
        $this->bestellijn = new ArrayCollection();
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
     * Set datum
     *
     * @param \DateTime $datum
     * @return Bestelling
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
     * Set klant
     *
     * @param \vino\PillarBundle\Entity\Klant $klant
     * @return Bestelling
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
     * Add bestellijn
     *
     * @param \vino\PillarBundle\Entity\Bestellijn $bestellijn
     * @return Bestelling
     */
    public function addBestellijn(\vino\PillarBundle\Entity\Bestellijn $bestellijn)
    {
        $this->bestellijn[] = $bestellijn;

        return $this;
    }

    /**
     * Remove bestellijn
     *
     * @param \vino\PillarBundle\Entity\Bestellijn $bestellijn
     */
    public function removeBestellijn(\vino\PillarBundle\Entity\Bestellijn $bestellijn)
    {
        $this->bestellijn->removeElement($bestellijn);
    }

    /**
     * Get bestellijn
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBestellijn()
    {
        return $this->bestellijn;
    }

    /**
     * Set leverwijze
     *
     * @param integer $leverwijze
     * @return Bestelling
     */
    public function setLeverwijze($leverwijze)
    {
        $this->leverwijze = $leverwijze;

        return $this;
    }

    /**
     * Get leverwijze
     *
     * @return integer 
     */
    public function getLeverwijze()
    {
        return $this->leverwijze;
    }

    /**
     * Set bestelstatus
     *
     * @param integer $bestelstatus
     * @return Bestelling
     */
    public function setBestelstatus($bestelstatus)
    {
        $this->bestelstatus = $bestelstatus;

        return $this;
    }

    /**
     * Get bestelstatus
     *
     * @return integer 
     */
    public function getBestelstatus()
    {
        return $this->bestelstatus;
    }
}
