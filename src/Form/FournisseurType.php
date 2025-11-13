<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Fournisseur;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FournisseurType form following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles supplier form creation
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized supplier form management
 * Following KISS principle: Simple form structure
 */
class FournisseurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque', TextType::class, [
                'label' => 'Marque',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom de la marque ou du fournisseur',
                ],
                'help' => 'Saisissez le nom de la marque ou du fournisseur',
            ])
            ->add('logo', UrlType::class, [
                'label' => 'Logo (URL)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://example.com/logo.png',
                ],
                'help' => 'URL de l\'image du logo du fournisseur',
            ])
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'label' => 'Produit associé',
                'placeholder' => 'Sélectionnez un produit',
                'attr' => [
                    'class' => 'form-select',
                ],
                'help' => 'Produit pour lequel ce fournisseur propose des solutions',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fournisseur::class,
        ]);
    }
}