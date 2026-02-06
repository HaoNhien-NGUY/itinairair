<?php

namespace App\Form;

use App\Entity\Trip;
use App\Service\TripService;
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

/**
 * @extends AbstractType<Trip>
 */
class TripType extends AbstractType
{

    public function __construct(private readonly TripService $tripService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ?Trip $trip */
        $trip = $options['data'];
        $isEdit = $trip && $trip->getId();

        $builder
            ->add('name', TextType::class, [
                'label' => $isEdit ? 'common.name' : 'form.trip.name',
                'attr' => [
                    'placeholder' => 'Ex: Escapade à Paris',
                    'icon' => 'mynaui:tag',
                    'autocomplete' => 'off',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nom du voyage est obligatoire'),
                    new Assert\Length(min: 3, max: 100, minMessage: 'Le nom doit faire au moins {{ limit }} caractères', maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères')
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Décrivez votre voyage, l\'ambiance, les objectifs...',
                    'rows' => 2,
                    'icon' => 'subway:paragraph-2',
                ],
                'constraints' => [
                    new Assert\Length(max: 1000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères')
                ]
            ])
            ->add('startDate', DateType::class, [
                'label' => false,
                'required' => true,
                'widget' => 'single_text',
                'attr' => [
                    'data-calendar-target' => 'startDaySelect',
                    'class' => 'hidden',
                ],
                'help' => 'Si vous connaissez déjà la date de début de votre voyage'
            ])
            ->add('endDate', DateType::class, [
                'label' => false,
                'required' => true,
                'widget' => 'single_text',
                'attr' => [
                    'data-calendar-target' => 'endDaySelect',
                    'class' => 'hidden',
                ],
                'help' => 'Si vous connaissez déjà la date de début de votre voyage'
            ])
            ->add('save', SubmitType::class, [
                'label' => $isEdit ? 'Modifier mon voyage' : 'Créer mon voyage',
                'attr' => [
                    'class' => 'block w-full py-2'
                ]
            ]);


        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var ?Trip $trip */
            $trip = $event->getData();

            if (!$trip) {
                return;
            }

            $startDate = $form->get('startDate')->getData();
            $endDate = $form->get('endDate')->getData();
            $requiredCount = $startDate->diff($endDate)->days + 1;

            $this->tripService->addOrRemoveTripDays($trip, $requiredCount);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trip::class,
        ]);
    }
}
