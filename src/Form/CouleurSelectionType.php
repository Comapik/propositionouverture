<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ConfPf;
use App\Entity\Couleur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Color selection form following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles color selection form
 * - Open/Closed: Can be extended without modification
 * 
 * Following DRY principle: Reusable form for color selection
 * Following KISS principle: Simple form structure for interior and exterior colors
 */
class CouleurSelectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('couleurInterieur', EntityType::class, [
                'class' => Couleur::class,
                'choice_label' => 'nom',
                'label' => 'Couleur intérieure',
                'required' => false,
                'placeholder' => 'Sélectionner une couleur intérieure...',
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('c')
                        ->orderBy('c.nom', 'ASC');
                },
                'attr' => [
                    'class' => 'form-select couleur-select',
                    'data-type' => 'interieur',
                ],
                'help' => 'Couleur pour la face intérieure',
                'choice_attr' => function(Couleur $couleur) {
                    $attr = [
                        'data-plaxage-laquage' => $couleur->getPlaxageLaquageId() ?? 0,
                    ];
                    
                    if ($couleur->isHexColor() && $couleur->getCodeHex()) {
                        $attr['data-color-hex'] = $couleur->getCodeHex();
                        $attr['data-color-type'] = 'hex';
                    } elseif ($couleur->getUrlImage()) {
                        $attr['data-color-image'] = $couleur->getUrlImage();
                        $attr['data-color-type'] = 'image';
                    }
                    
                    return $attr;
                },
            ])
            ->add('couleurExterieur', EntityType::class, [
                'class' => Couleur::class,
                'choice_label' => 'nom',
                'label' => 'Couleur extérieure',
                'required' => false,
                'placeholder' => 'Sélectionner une couleur extérieure...',
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('c')
                        ->orderBy('c.nom', 'ASC');
                },
                'attr' => [
                    'class' => 'form-select couleur-select',
                    'data-type' => 'exterieur',
                ],
                'help' => 'Couleur pour la face extérieure',
                'choice_attr' => function(Couleur $couleur) {
                    $attr = [
                        'data-plaxage-laquage' => $couleur->getPlaxageLaquageId() ?? 0,
                    ];
                    
                    if ($couleur->isHexColor() && $couleur->getCodeHex()) {
                        $attr['data-color-hex'] = $couleur->getCodeHex();
                        $attr['data-color-type'] = 'hex';
                    } elseif ($couleur->getUrlImage()) {
                        $attr['data-color-image'] = $couleur->getUrlImage();
                        $attr['data-color-type'] = 'image';
                    }
                    
                    return $attr;
                },
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Continuer vers les détails',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConfPf::class,
        ]);
    }
}