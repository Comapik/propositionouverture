<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Client form type following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles client form only
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on Form abstractions
 * 
 * Following DRY principle: Reusable form configuration
 * Following KISS principle: Simple form structure
 */
final class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du client',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom complet du client'
                ],
                'help' => 'Nom complet du client (obligatoire)'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'client@exemple.com'
                ],
                'required' => false,
                'help' => 'Adresse email du client (optionnelle)'
            ])
            ->add('tel', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '0123456789',
                    'pattern' => '[0-9]{10}',
                    'maxlength' => '10'
                ],
                'required' => false,
                'help' => 'Numéro de téléphone (10 chiffres, optionnel)'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}