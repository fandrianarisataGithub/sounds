<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                "attr" => [
                    "data-placeholder" => "Adresse mail",
                    "class" => "form-control",
                    "placeholder" => "Adresse email",
                    "id" => "mail"
                ]
            ])
            ->add('password', PasswordType::class, [
                "attr" => [
                    "data-placeholder" => "Password",
                    "class" => "form-control",
                    "placeholder" => "Password",
                    "id" => "password"
                ]
            ])
            ->add('c_password', PasswordType::class, [
                "attr" => [
                    "data-placeholder" => "Confirmer le password",
                    "class" => "form-control",
                    "placeholder" => "Confirmer le password",
                    "id" => "c_password"
                ]
            ])
            ->add('nom', TextType::class, [
                "attr" => [
                    "data-placeholder" => "Nom",
                    "class" => "form-control",
                    "placeholder" => "Nom",
                    "id" => "nom"
                ]
            ])
            ->add('prenom', TextType::class, [
                "attr" => [
                    "data-placeholder" => "Prénom",
                    "class" => "form-control",
                    "placeholder" => "Prénom",
                    "id" => "prenom"
                ]
            ])
            
            ->add('username', TextType::class, [
                "attr" => [
                    "data-placeholder" => "Username",
                    "class" => "form-control",
                    "placeholder" => "Username",
                    "id" => "username"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
