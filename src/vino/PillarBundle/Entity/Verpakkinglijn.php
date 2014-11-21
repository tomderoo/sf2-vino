<?php
// src/vino/PillarBundle/Entity/Verpakkinglijn.php

namespace vino\PillarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="vino\PillarBundle\Entity\Repository\VerpakkinglijnRepository")
 * @ORM\Table(name="verpakkinglijn")
 * @ORM\HasLifecycleCallbacks
 */

class Verpakkinglijn {
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Bestellijn", inversedBy="verpakkinglijn")
     * @ORM\JoinColumn(name="bestellijn_id", referencedColumnName="id")
     */
    protected $bestellijn;
    
    /**
     * @ORM\ManyToOne(targetEntity="Verpakking", inversedBy="verpakkinglijn")
     * @ORM\JoinColumn(name="verpakking_id", referencedColumnName="id")
     */
    protected $verpakking;
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $aantal;
    


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
     * @return Verpakkinglijn
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
     * Set bestellijn
     *
     * @param \vino\PillarBundle\Entity\Bestellijn $bestellijn
     * @return Verpakkinglijn
     */
    public function setBestellijn(\vino\PillarBundle\Entity\Bestellijn $bestellijn = null)
    {
        $this->bestellijn = $bestellijn;

        return $this;
    }

    /**
     * Get bestellijn
     *
     * @return \vino\PillarBundle\Entity\Bestellijn 
     */
    public function getBestellijn()
    {
        return $this->bestellijn;
    }

    /**
     * Set verpakking
     *
     * @param \vino\PillarBundle\Entity\Verpakking $verpakking
     * @return Verpakkinglijn
     */
    public function setVerpakking(\vino\PillarBundle\Entity\Verpakking $verpakking = null)
    {
        $this->verpakking = $verpakking;

        return $this;
    }

    /**
     * Get verpakking
     *
     * @return \vino\PillarBundle\Entity\Verpakking 
     */
    public function getVerpakking()
    {
        return $this->verpakking;
    }
}
