<?php


namespace App\Form\TravelItem;

use App\Entity\Accommodation;
use App\Entity\Activity;
use App\Entity\Day;
use App\Entity\Flight;
use App\Entity\Trip;
use App\Enum\ItemStatus;
use App\Repository\DayRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FlightType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Trip $trip */
        $trip = $options['trip'];

        $builder
            ->add('flightNumber', TextType::class, [
                'required' => false,
                'label'    => 'Numero du vol'
            ])
            ->add('startDay', EntityType::class, [
                'class' => Day::class,
                'query_builder' => function (DayRepository $dayRepository) use ($trip) {
                    return $dayRepository->createQueryBuilder('d')
                        ->where('d.trip = :trip')
                        ->setParameter('trip', $trip)
                        ->orderBy('d.position', 'ASC');
                },
                'data' => null,
                'attr' => [
                    'data-calendar-target' => 'startDaySelect',
                    'class' => 'hidden'
                ],
                'choice_label' => fn(Day $day) => $day->getPosition(),
            ])
            ->add('endDay', EntityType::class, [
                'class' => Day::class,
                'query_builder' => function (DayRepository $dayRepository) use ($trip) {
                    return $dayRepository->createQueryBuilder('d')
                        ->where('d.trip = :trip')
                        ->setParameter('trip', $trip)
                        ->orderBy('d.position', 'ASC');
                },
                'data' => null,
                'attr' => [
                    'data-calendar-target' => 'startDaySelect', // startDay because we are using 2 separate calendars
                    'class' => 'hidden'
                ],
                'choice_label' => fn(Day $day) => $day->getPosition(),
            ])
            ->add('departureAirportCode', TextType::class, [
                'attr' => ['placeholder' => 'Code IATA (ex: CDG)', 'maxLength' => 4],
                'property_path' => 'departureAirport[code]',
                'required' => false,
            ])
            ->add('departureTerminal', TextType::class, [
                'attr' => ['placeholder' => 'Terminal'],
                'property_path' => 'departureAirport[terminal]',
                'required' => false,
            ])
            ->add('arrivalAirportCode', TextType::class, [
                'attr' => ['placeholder' => 'Code IATA (ex: CDG)', 'maxLength' => 4],
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
            ])
            ->add('endTime', TimeType::class, [
                'input'  => 'datetime',
                'widget' => 'single_text',
                'required' => false,
            ]);


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
