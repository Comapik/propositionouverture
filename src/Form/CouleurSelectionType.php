<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ConfPf;
use App\Entity\Couleur;
use App\Form\DataTransformer\CouleurTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManagerInterface;

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
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }
    
    private function getCouleurChoices(): array
    {
        $choices = [];
        
        // Ajouter les couleurs spéciales en premier
        $choices['--- Couleurs rapides ---'] = [];
        $choices['--- Couleurs rapides ---']['Blanc'] = 'special_blanc';
        $choices['--- Couleurs rapides ---']['Crème'] = 'special_creme';
        
        // Ajouter les couleurs de la base de données
        $couleurs = $this->entityManager->getRepository(Couleur::class)
            ->createQueryBuilder('c')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
            
        if (!empty($couleurs)) {
            $choices['--- Couleurs catalogue ---'] = [];
            foreach ($couleurs as $couleur) {
                $choices['--- Couleurs catalogue ---'][$couleur->getNom()] = $couleur->getId();
            }
        }
        
        return $choices;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $couleurChoices = $this->getCouleurChoices();
        $couleurTransformer = new CouleurTransformer($this->entityManager);
        
        $builder
            ->add('couleurInterieur', ChoiceType::class, [
                'label' => 'Couleur intérieure',
                'required' => false,
                'placeholder' => 'Sélectionner une couleur intérieure...',
                'choices' => $couleurChoices,
                'attr' => [
                    'class' => 'form-select couleur-select',
                    'data-type' => 'interieur',
                ],
                'help' => 'Couleur pour la face intérieure',
                'choice_attr' => function($choice, $key, $value) {
                    return $this->getChoiceAttributes($value);
                },
            ])
            ->add('couleurExterieur', ChoiceType::class, [
                'label' => 'Couleur extérieure',
                'required' => false,
                'placeholder' => 'Sélectionner une couleur extérieure...',
                'choices' => $couleurChoices,
                'attr' => [
                    'class' => 'form-select couleur-select',
                    'data-type' => 'exterieur',
                ],
                'help' => 'Couleur pour la face extérieure',
                'choice_attr' => function($choice, $key, $value) {
                    return $this->getChoiceAttributes($value);
                },
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Continuer vers les détails',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg',
                ],
            ]);
            
        // Ajouter les transformers pour convertir les valeurs en entités Couleur
        $builder->get('couleurInterieur')->addModelTransformer($couleurTransformer);
        $builder->get('couleurExterieur')->addModelTransformer($couleurTransformer);
    }
    
    private function getChoiceAttributes($value): array
    {
        $attr = [];
        
        if (str_starts_with($value, 'special_')) {
            $attr['data-color-type'] = 'special';
            if ($value === 'special_blanc') {
                $attr['data-color-hex'] = '#FFFFFF';
            } elseif ($value === 'special_creme') {
                $attr['data-color-hex'] = '#F5F5DC';
            }
        } else {
            // Couleur normale de la base
            $couleur = $this->entityManager->getRepository(Couleur::class)->find($value);
            if ($couleur) {
                $attr['data-plaxage-laquage'] = $couleur->getPlaxageLaquageId() ?? 0;
                
                if ($couleur->isHexColor() && $couleur->getCodeHex()) {
                    $attr['data-color-hex'] = $couleur->getCodeHex();
                    $attr['data-color-type'] = 'hex';
                } elseif ($couleur->getUrlImage()) {
                    $attr['data-color-image'] = $couleur->getUrlImage();
                    $attr['data-color-type'] = 'image';
                }
            }
        }
        
        return $attr;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConfPf::class,
        ]);
    }
}