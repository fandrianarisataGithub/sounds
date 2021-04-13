<?php

namespace App\Form;

use App\Entity\DonneeMensuelle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DonneeMensuelleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('stock', TextType::class, [
                'required' => false,
            ])
            ->add('cost_restaurant_value', TextType::class, [
                'required' => false,
            ])
            ->add('kpi_adr', TextType::class, [
                'required' => false,
            ])
            ->add('kpi_revp', TextType::class, [
                'required' => false,
            ])
            ->add('cost_restaurant_pourcent', TextType::class, [
                'required' => false,
            ])
            ->add('cost_electricite_value', TextType::class, [
                'required' => false,
            ])
            ->add('cost_electricite_pourcent', TextType::class, [
                'required' => false,
            ])
            ->add('cost_eau_value', TextType::class, [
                'required' => false,
            ])
            ->add('cost_eau_pourcent', TextType::class, [
                'required' => false,
            ])
            ->add('cost_gasoil_value', TextType::class, [
                'required' => false,
            ])
            ->add('cost_gasoil_pourcent', TextType::class, [
                'required' => false,
            ])
            ->add('salaire_brute_value', TextType::class, [
                'required' => false,
            ])
            ->add('salaire_brute_pourcent', TextType::class, [
                'required' => false,
            ])
            ->add('sqn_interne', TextType::class, [
                'required' => false,
            ])
            ->add('sqn_booking', TextType::class, [
                'required' => false,
            ])
            ->add('sqn_tripadvisor', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DonneeMensuelle::class,
            'attr' => ['id' => 'form_mensuelle']
        ]);
    }
}
