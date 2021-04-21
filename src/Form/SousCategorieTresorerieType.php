<?php

namespace App\Form;

use App\Entity\CategoryTresorerie;
use App\Entity\SousCategorieTresorerie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SousCategorieTresorerieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            
            ->add('categorie', EntityType::class, [
                'class' => CategoryTresorerie::class,
                'choice_label'=> 'nom'
            ])
            ->add('nom')
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SousCategorieTresorerie::class,
        ]);
    }
}
