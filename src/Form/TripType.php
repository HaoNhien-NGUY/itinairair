<?php

namespace App\Form;

use App\Entity\Day;
use App\Entity\Trip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TripType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du voyage',
                'attr' => [
                    'placeholder' => 'Ex: Escapade à Paris',
                    'class' => 'form-control form-control-lg'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom du voyage est obligatoire']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 100,
                        'minMessage' => 'Le nom doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])

            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Décrivez votre voyage, l\'ambiance, les objectifs...',
                    'class' => 'form-control',
                    'rows' => 4
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 1000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])

            ->add('startDate', DateType::class, [
                'label' => 'Date de depart',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'min' => date('Y-m-d') // Pas de date dans le passé
                ],
                'help' => 'Si vous connaissez déjà la date de début de votre voyage'
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Date de fin (optionnelle)',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'min' => date('Y-m-d') // Pas de date dans le passé
                ],
                'help' => 'Si vous connaissez déjà la date de début de votre voyage'
            ])


//            ->add('collaborators', TextareaType::class, [
//                'label' => 'Collaborateurs (emails)',
//                'required' => false,
//                'attr' => [
//                    'class' => 'form-control collaborators-field',
//                    'placeholder' => 'ami1@email.com, ami2@email.com...',
//                    'rows' => 2,
//                    'style' => 'display: none;' // Masqué par défaut
//                ],
//                'help' => 'Emails des personnes qui pourront modifier ce voyage (séparés par des virgules)',
//                'mapped' => false // Pas directement mappé à l'entité
//            ])


            ->add('save', SubmitType::class, [
                'label' => '🚀 Créer mon voyage',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg w-100 mt-4'
                ]
            ]);


        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();
            $nbDays = $startDate->diff($endDate)->days;
            $trip = $event->getData();

            for ($i = 0; $i <= $nbDays; $i++) {
                $trip->addDay((new Day())->setPosition($i + 1));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trip::class,
        ]);
    }
}
