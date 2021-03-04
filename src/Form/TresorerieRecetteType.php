<?php

namespace App\Form;

use App\Entity\TresorerieRecette;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TresorerieRecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date')
            ->add('designation')
            ->add('num_sage')
            ->add('mode_paiement')
            ->add('compte_bancaire')
            ->add('Monnaie')
            ->add('paiement')
            ->add('id_pro')
            ->add('nom_client')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TresorerieRecette::class,
        ]);
    }
}
