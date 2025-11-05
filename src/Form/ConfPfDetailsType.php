<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ConfPf;
use App\Entity\Fournisseur;
use App\Entity\Systeme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Configuration details form following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles configuration details form
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Reusable form for configuration details
 * Following KISS principle: Simple form structure
 */
class ConfPfDetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 2',
                    'min' => 1,
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'La quantité est obligatoire'),
                    new Assert\Positive(message: 'La quantité doit être positive'),
                ],
                'help' => 'Nombre d\'éléments à commander',
            ])
            ->add('largeur', IntegerType::class, [
                'label' => 'Largeur (mm)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 1200',
                    'min' => 1,
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'La largeur est obligatoire'),
                    new Assert\Positive(message: 'La largeur doit être positive'),
                    new Assert\Range(
                        min: 100,
                        max: 5000,
                        notInRangeMessage: 'La largeur doit être comprise entre {{ min }}mm et {{ max }}mm'
                    ),
                ],
                'help' => 'Largeur en millimètres (entre 100mm et 5000mm)',
            ])
            ->add('hauteur', IntegerType::class, [
                'label' => 'Hauteur (mm)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 1500',
                    'min' => 1,
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'La hauteur est obligatoire'),
                    new Assert\Positive(message: 'La hauteur doit être positive'),
                    new Assert\Range(
                        min: 100,
                        max: 5000,
                        notInRangeMessage: 'La hauteur doit être comprise entre {{ min }}mm et {{ max }}mm'
                    ),
                ],
                'help' => 'Hauteur en millimètres (entre 100mm et 5000mm)',
            ])
            ->add('fournisseur', EntityType::class, [
                'class' => Fournisseur::class,
                'choice_label' => 'marque',
                'label' => 'Fournisseur',
                'required' => false,
                'placeholder' => 'Sélectionner un fournisseur...',
                'choices' => $options['fournisseurs'] ?? [],
                'attr' => [
                    'class' => 'form-select',
                    'data-fournisseur-select' => 'true',
                ],
                'help' => 'Marque/fournisseur du produit (optionnel)',
            ])
            ->add('systeme', EntityType::class, [
                'class' => Systeme::class,
                'choice_label' => 'nom',
                'label' => 'Système et capotage',
                'required' => false,
                'placeholder' => 'Sélectionner un système...',
                'attr' => [
                    'class' => 'form-select',
                    'data-systeme-select' => 'true',
                ],
                'help' => 'Type de système et capotage (dépend du fournisseur)',
            ])
            ->add('position', TextType::class, [
                'label' => 'Position',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: RDC - Salon, 1er étage - Chambre 1...',
                    'maxlength' => 255,
                ],
                'constraints' => [
                    new Assert\Length(
                        max: 255,
                        maxMessage: 'La position ne peut pas dépasser {{ limit }} caractères'
                    ),
                ],
                'help' => 'Emplacement ou position de l\'élément (optionnel)',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer la configuration',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConfPf::class,
            'fournisseurs' => [],
        ]);

        $resolver->setAllowedTypes('fournisseurs', 'array');
    }
}