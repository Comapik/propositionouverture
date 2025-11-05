<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Client;
use App\Entity\Projet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Projet form type following SOLID principles.
 * 
 * Following SOLID principles:
 * - Single Responsibility: Handles project form only
 * - Open/Closed: Can be extended without modification
 * - Dependency Inversion: Depends on Form abstractions
 * 
 * Following DRY principle: Reusable form configuration
 * Following KISS principle: Simple form structure
 */
final class ProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('refClient', TextType::class, [
                'label' => 'Référence Client',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez la référence client'
                ],
                'help' => 'Référence unique du client pour ce projet'
            ])
            ->add('clientChoice', ChoiceType::class, [
                'label' => 'Option Client',
                'choices' => [
                    'Client existant' => 'existing',
                    'Nouveau client' => 'new'
                ],
                'expanded' => true,
                'multiple' => false,
                'data' => 'existing',
                'mapped' => false,
                'attr' => [
                    'class' => 'client-choice-radio'
                ],
                'help' => 'Choisissez si vous voulez sélectionner un client existant ou en créer un nouveau'
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => function(Client $client) {
                    return $client->getNom() . ($client->getEmail() ? ' (' . $client->getEmail() . ')' : '');
                },
                'label' => 'Client existant',
                'placeholder' => 'Sélectionnez un client',
                'attr' => [
                    'class' => 'form-select client-existing-field'
                ],
                'required' => false,
                'help' => 'Client existant associé à ce projet'
            ])
            ->add('newClient', ClientType::class, [
                'label' => 'Nouveau client',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'client-new-field',
                    'style' => 'display: none;'
                ]
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Adresse ou lieu du projet'
                ],
                'required' => false,
                'help' => 'Adresse ou localisation du projet'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Description détaillée du projet'
                ],
                'required' => false,
                'help' => 'Description détaillée du projet (optionnelle)'
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            
            // Si "nouveau client" est sélectionné, on s'assure que le client existant n'est pas requis
            if (isset($data['clientChoice']) && $data['clientChoice'] === 'new') {
                $form = $event->getForm();
                $form->add('client', EntityType::class, [
                    'class' => Client::class,
                    'choice_label' => function(Client $client) {
                        return $client->getNom() . ($client->getEmail() ? ' (' . $client->getEmail() . ')' : '');
                    },
                    'label' => 'Client existant',
                    'placeholder' => 'Sélectionnez un client',
                    'attr' => [
                        'class' => 'form-select client-existing-field'
                    ],
                    'required' => false,
                    'mapped' => false
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}