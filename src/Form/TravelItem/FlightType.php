<?php

namespace App\Form\TravelItem;

use App\Entity\Day;
use App\Entity\Flight;
use App\Entity\Trip;
use App\Repository\DayRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<Flight>
 */
class FlightType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Trip $trip */
        $trip = $options['trip'];

        $builder
            ->add('flightNumber', TextType::class, [
                'required' => false,
                'label'    => 'transport.airplane.flight_number',
                'attr' => ['placeholder' => 'ex: AF012', 'maxLength' => 5],
            ])
            ->add('startDay', EntityType::class, [
                'class' => Day::class,
                'required' => false,
                'placeholder' => '',
                'constraints' => [new Assert\NotBlank()],
                'query_builder' => function (DayRepository $dayRepository) use ($trip) {
                    return $dayRepository->createQueryBuilder('d')
                        ->where('d.trip = :trip')
                        ->setParameter('trip', $trip)
                        ->orderBy('d.position', 'ASC');
                },
                'attr' => [
                    'data-calendar-target' => 'startDaySelect',
                    'class' => 'hidden',
                ],
                'choice_label' => fn (Day $day) => $day->getPosition(),
            ])
            ->add('endDay', EntityType::class, [
                'class' => Day::class,
                'placeholder' => '',
                'required' => false,
                'query_builder' => function (DayRepository $dayRepository) use ($trip) {
                    return $dayRepository->createQueryBuilder('d')
                        ->where('d.trip = :trip')
                        ->setParameter('trip', $trip)
                        ->orderBy('d.position', 'ASC');
                },
                'attr' => [
                    'data-calendar-target' => 'startDaySelect',
                    'class' => 'hidden',
                ],
                'choice_label' => fn (Day $day) => $day->getPosition(),
            ])
            ->add('departureAirportCode', TextType::class, [
                'attr' => ['placeholder' => 'common.airport', 'maxLength' => 4],
                'property_path' => 'departureAirport[code]',
                'required' => false,
            ])
            ->add('departureTerminal', TextType::class, [
                'attr' => ['placeholder' => 'Terminal'],
                'property_path' => 'departureAirport[terminal]',
                'required' => false,
            ])
            ->add('arrivalAirportCode', TextType::class, [
                'attr' => ['placeholder' => 'common.airport', 'maxLength' => 4],
                'property_path' => 'arrivalAirport[code]',
                'required' => false,
            ])
            ->add('arrivalTerminal', TextType::class, [
                'attr' => ['placeholder' => 'Terminal'],
                'property_path' => 'arrivalAirport[terminal]',
                'required' => false,
            ])
            ->add('startTime', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-input py-1.5'],
            ])
            ->add('endTime', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-input py-1.5'],
            ])
            ->remove('status');


        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Flight $flight */
            $flight = $event->getData();
            $flight->setName($flight->getFlightNumber() ?: '');

            if ($flight->getStartDay() === $flight->getEndDay()) {
                $flight->setEndDay(null);
            }
        });
    }

    public function getParent(): string
    {
        return AbstractTravelItemType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Flight::class,
        ]);
    }
}
