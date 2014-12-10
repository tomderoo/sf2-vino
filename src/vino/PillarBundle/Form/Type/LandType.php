<?php
// src/vino/PillarBundle/Form/Type/CategorieType.php
namespace vino\PillarBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('naam', 'text', array ('attr' => array('class' => 'form-control')));
        $builder->add('omschrijving', 'textarea', array ('attr' => array('class' => 'form-control')));
        $builder->add('save', 'submit', array('attr' => array('class' => 'btn btn-info'), 'label' => 'Opslaan'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'vino\PillarBundle\Entity\Land'
        ));
    }

    public function getName()
    {
        return 'landform';
    }
}