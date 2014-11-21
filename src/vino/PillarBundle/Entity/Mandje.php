<?php
// src/vino/PillarBundle/Entity/Mandje.php

namespace vino\PillarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Mandje {
    protected $klant;
    
    protected $bestelling = Array();
    
    public function getKlant() {
        return $this->klant;
    }
    
    public function setKlant($klant) {
        $this->klant = $klant;
        return $this->klant;
    }
    
    public function getBestelling() {
        return $this->bestelling;
    }
    
    public function setBestelling($bestelling) {
        $this->bestelling = $bestelling;
        return $this->bestelling;
    }
}