<?php

namespace App\Form;

use App\Entity\DonneeDuJour;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class DonneeDuJourType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('heb_to', TextType::class, [
                "attr" => [
                    "class" => "input_pourcent",
                ]
            ])
           
            ->add('heb_ca', TextType::class, [
                "attr" => [
                    "class" => "input_nombre",
                    "maxlength" => "23"
                ]
            ]) 
            
            ->add('res_n_couvert', TextType::class, [
                "attr" => [
                    "class"=> "input_nombre",
                    "max" => "100",
                    "mawlength" => "18"
                ]
            ])
            ->add('res_ca', TextType::class, [
                "attr" => [
                    "class" => "input_nombre",
                    "maxlength" => "23"
                ]
            ])
            ->add('res_p_dej', TextType::class, [
                "attr" => [
                    "class" => "input_nombre",
                    "max" => "100",
                    "mawlength" => "18"
                ]
            ])
            ->add('res_dej', TextType::class, [
                "attr" => [
                    "class" => "input_nombre",
                    "max" => "100",
                    "mawlength" => "18"
                ]
            ])
            ->add('res_dinner', TextType::class, [
                "attr" => [
                    "class" => "input_nombre",
                    "max" => "100",
                    "mawlength" => "18"
                ]
            ])
            ->add('spa_ca', TextType::class, [
                "attr" => [
                    "class" => "input_nombre",
                    "maxlength" => "23"
                ]
            ])
            ->add('spa_n_abonne', TextType::class, [
                "attr" => [
                    "class" => "input_nombre",
                    "max" => "100",
                    "mawlength" => "18"
                ]
            ])
            ->add('spa_c_unique', TextType::class, [
                "attr" => [
                    "class" => "input_nombre",
                    "max" => "100",
                    "mawlength" => "18"
                ]
            ])
            ->add('crj_direction', TextareaType::class, [
                'required' => false,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('crj_service_rh', TextareaType::class, [
                'required' => false,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('crj_commercial', TextareaType::class, [
                'required' => false,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('crj_comptable', TextareaType::class, [
                'required' => false,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('crj_reception', TextareaType::class, [
                'required' => false,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('crj_restaurant', TextareaType::class, [
                'required' => false,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('crj_spa', TextareaType::class, [
                'required' => false,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('crj_s_technique', TextareaType::class, [
                'required' => false,
                "attr" => [
                    "class" => "form-control"
                ]
            ])
            ->add('crj_litiges', TextareaType::class, [
                'required' => false,
                "attr" => [
                    "class" => "form-control"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DonneeDuJour::class,
        ]);
    }
}
