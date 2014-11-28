<?php
// src/vino/PillarBundle/Form/DataTransformer/KlantToIdTransformer.php
// zoals gepropageerd in cookbook: http://symfony.com/doc/2.3/cookbook/form/data_transformers.html

namespace vino\PillarBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use vino\PillarBundle\Entity\Klant;

class KlantToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (klant) to number (id).
     *
     * @param  Issue|null $issue
     * @return integer
     */
    public function transform($klant)
    {
        if (null === $klant) {
            return null;
        }

        return $klant->getId();
    }

    /**
     * Transforms a number (id) to an object (klant).
     *
     * @param  integer $number
     *
     * @return Klant|null
     *
     * @throws TransformationFailedException if object (klant) is not found.
     */
    public function reverseTransform($number)
    {
        if (!$number) {
            return null;
        }

        $klant = $this->om
            ->getRepository('vinoPillarBundle:Klant')
            //->findOneBy(array('number' => $number))
            ->findOneById($number)
        ;

        if (null === $klant) {
            throw new TransformationFailedException(sprintf(
                'Klant met id "%s" bestaat niet!',
                $number
            ));
        }

        return $klant;
    }
}