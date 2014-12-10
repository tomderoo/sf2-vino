<?php
// src/vino/PillarBundle/Form/Type/KlantType.php
namespace vino\PillarBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class KlantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('vNaam', 'text', array (
            'label' => 'Voornaam',
            'attr' => array('class' => 'form-control')
            ));
        $builder->add('aNaam', 'text', array (
            'label' => 'Familienaam',
            'attr' => array('class' => 'form-control')
            ));
        $builder->add('straat', 'text', array (
            'attr' => array('class' => 'form-control')
            ));
        $builder->add('huisnr', 'text', array (
            'label' => 'Huisnummer',
            'attr' => array('class' => 'form-control')
            ));
        $builder->add('busnr', 'text', array (
            'label' => 'Busnummer',
            'required' => false,
            'attr' => array('class' => 'form-control')
            ));
        $builder->add('postcode', 'text', array (
            'attr' => array('class' => 'form-control')
            ));
        $builder->add('gemeente', 'text', array (
            'attr' => array('class' => 'form-control')
            ));
        $builder->add('email', 'email', array (
            'attr' => array('class' => 'form-control')
            ));
        $builder->add('paswoord', 'password', array (
            'attr' => array('class' => 'form-control', 'maxlength' => '20')
            ));
        $builder->add('save', 'submit', array('attr' => array('class' => 'btn btn-info'), 'label' => 'Opslaan'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'vino\PillarBundle\Entity\Klant'
        ));
    }

    public function getName()
    {
        return 'klantform';
    }
}