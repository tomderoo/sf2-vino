<?php
// src/vino/PillarBundle/Entity/Wijn.php

namespace vino\PillarBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;    // nodig om relatiemappings te kunnen maken
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="vino\PillarBundle\Entity\Repository\WijnRepository")
 * @ORM\Table(name="wijn")
 * @ORM\HasLifecycleCallbacks
 */

class Wijn {
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $naam;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $jaar;
    
    /**
     * @ORM\Column(type="text")
     */
    protected $omschrijving;
    
    /**
     * @ORM\ManyToOne(targetEntity="Categorie", inversedBy="wijn")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="id")
     */
    protected $categorie;
    
    /**
     * @ORM\ManyToOne(targetEntity="Land", inversedBy="wijn")
     * @ORM\JoinColumn(name="land_id", referencedColumnName="id")
     */
    protected $land;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $prijs;
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $slug;
    
    /**
     * @ORM\OneToMany(targetEntity="Review", mappedBy="wijn")
     */
    protected $review;  // deze is nodig om aan te geven dat er many "review" bij one "wijn" horen
    
    /**
     * @ORM\OneToMany(targetEntity="Bestellijn", mappedBy="wijn")
     */
    protected $bestellijn;  // deze is nodig om aan te geven dat er many "bestellijn" bij one "wijn" horen
    
    /* FILE UPLOAD */
    
    /**
     * 
     */
    private $image;
    
    private $temp;
    
    /**
     * Sets image.
     *
     * @param UploadedFile $$image
     */
    public function setImage(UploadedFile $image = null)
    {
        $this->image = $image;
        // check for old path
        if (isset($this->imgpath)) {
            // store the old name to delete after the update
            $this->temp = $this->imgpath;
            $this->imgpath = null;
        } else {
            $this->imgpath = 'initial';
        }
    }
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getImage()) {
            // do whatever you want to generate a unique name
            //$filename = sha1(uniqid(mt_rand(), true));
            //$this->imgpath = $filename.'.'.$this->getImage()->guessExtension();
            // or get the filename from the slug ... :p
            $this->imgpath = $this->getSlug() . '.' . $this->getImage()->guessExtension();
        }
    }
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getImage()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getImage()->move($this->getUploadRootDir(), $this->imgpath);

        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->temp);
            // clear the temp image path
            $this->temp = null;
        }
        $this->image = null;
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $file = $this->getAbsolutePath();
        if ($file) {
            unlink($file);
        }
    }

    /**
     * Get image.
     *
     * @return UploadedFile
     */
    public function getImage()
    {
        return $this->image;
    }
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imgpath;
    
    public function setImgpath($imgpath) {
        $this->imgpath = $imgpath;
    }
    
    public function getImgpath() {
        return $this->imgpath;
    }
    
    public function getAbsolutePath() {
        return null === $this->imgpath ? null : $this->getUploadRootDir().'/'.$this->imgpath;
    }
    
    public function getWebPath() {
        return null === $this->imgpath ? null : $this->getUploadDir().'/'.$this->imgpath;
    }
    
    public function getUploadRootDir() {
        // absolute dir path here uploaded docs should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }
    
    public function getUploadDir() {
        // get rid of the __DIR__ part for viewing reasons
        return 'uploads/img';
    }
    
    public function __construct(){
        $this->review = new ArrayCollection();
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
     * Set naam
     *
     * @param string $naam
     * @return Wijn
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
     * Set jaar
     *
     * @param integer $jaar
     * @return Wijn
     */
    public function setJaar($jaar)
    {
        $this->jaar = $jaar;

        return $this;
    }

    /**
     * Get jaar
     *
     * @return integer 
     */
    public function getJaar()
    {
        return $this->jaar;
    }

    /**
     * Set omschrijving
     *
     * @param string $omschrijving
     * @return Wijn
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
     * Set prijs
     *
     * @param integer $prijs
     * @return Wijn
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
     * Set slug
     *
     * @param string $slug
     * @return Wijn
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set categorie
     *
     * @param \vino\PillarBundle\Entity\Categorie $categorie
     * @return Wijn
     */
    public function setCategorie(\vino\PillarBundle\Entity\Categorie $categorie = null)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return \vino\PillarBundle\Entity\Categorie 
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set land
     *
     * @param \vino\PillarBundle\Entity\Land $land
     * @return Wijn
     */
    public function setLand(\vino\PillarBundle\Entity\Land $land = null)
    {
        $this->land = $land;

        return $this;
    }

    /**
     * Get land
     *
     * @return \vino\PillarBundle\Entity\Land 
     */
    public function getLand()
    {
        return $this->land;
    }

    /**
     * Add review
     *
     * @param \vino\PillarBundle\Entity\Review $review
     * @return Wijn
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
     * Add bestellijn
     *
     * @param \vino\PillarBundle\Entity\Bestellijn $bestellijn
     * @return Wijn
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
    
    protected $rating = 0;
    
    public function getRating() {
        return $this->rating;
    }
    
    public function setRating($rating = 0) {
        $this->rating = $rating;
        return $this->rating;
    }
}
