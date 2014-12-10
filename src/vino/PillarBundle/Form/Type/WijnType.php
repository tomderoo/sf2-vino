<?php
// src/vino/PillarBundle/Form/Type/WijnType.php
namespace vino\PillarBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WijnType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('naam', 'text', array ('attr' => array('class' => 'form-control')));
        $builder->add('jaar', 'integer', array ('attr' => array('class' => 'form-control')));
        $builder->add('omschrijving', 'textarea', array ('attr' => array('class' => 'form-control')));
        $builder->add('categorie', 'entity', array(
            'attr' => array('class' => 'form-control'),
            'class' => 'vinoPillarBundle:Categorie',
            'property' => 'naam',
            'expanded' => false,
            'multiple' => false,
            ));
        $builder->add('land', 'entity', array(
            'attr' => array('class' => 'form-control'),
            'class' => 'vinoPillarBundle:Land',
            'property' => 'naam',
            'expanded' => false,
            'multiple' => false,
        ));
        $builder->add('prijs', 'integer', array ('attr' => array('class' => 'form-control')));
        $builder->add('image', 'file', array ('required' => false, 'attr' => array('class' => 'form-control')));
        $builder->add('save', 'submit', array('attr' => array('class' => 'btn btn-info'), 'label' => 'Opslaan'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'vino\PillarBundle\Entity\Wijn'
        ));
    }

    public function getName()
    {
        return 'wijnform';
    }
}