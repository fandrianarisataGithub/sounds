<?php

namespace App\Form;

use App\Entity\TresorerieDepense;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TresorerieDepenseType extends AbstractType
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
            ->add('num_compte')
            ->add('nom_fournisseur')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TresorerieDepense::class,
        ]);
    }
}
