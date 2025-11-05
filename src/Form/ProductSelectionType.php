<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ProductSelectionType form following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles product selection form
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Centralized product selection form
 * Following KISS principle: Simple product selection form
 */
class ProductSelectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez un produit...',
                'attr' => [
                    'class' => 'form-select form-select-lg',
                ],
                'label' => 'Produit',
                'label_attr' => [
                    'class' => 'form-label fs-5 fw-bold',
                ],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}