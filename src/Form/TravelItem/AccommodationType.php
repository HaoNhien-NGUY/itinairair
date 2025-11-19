<?php


namespace App\Form\TravelItem;

use App\Entity\Accommodation;
use App\Entity\Day;
use App\Entity\Trip;
use App\Repository\DayRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Trip $trip */
        $trip = $options['trip'];

        $builder
//            ->add('name', TextType::class)
            ->add('startDay', EntityType::class, [
                'class' => Day::class,
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
                'choice_label' => fn(Day $day) => $day->getTitle(),
            ])
            ->add('endDay', EntityType::class, [
                'class' => Day::class,
                'query_builder' => function (DayRepository $dayRepository) use ($trip) {
                    return $dayRepository->createQueryBuilder('d')
                        ->where('d.trip = :trip')
                        ->setParameter('trip', $trip)
                        ->orderBy('d.position', 'ASC');
                },
                'attr' => [
                    'data-calendar-target' => 'endDaySelect',
                    'class' => 'hidden',
                ],
                'choice_label' => fn(Day $day) => $day->getTitle(),
                'required' => false,
            ])
            ->add('place', PlaceType::class, [
                'required' => true,
                'label'    => false,
            ]);


        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Accommodation $accommodation */
            $accommodation = $event->getData();
            $accommodation->setName($accommodation->getPlace()->getName());
        });
    }

    public function getParent(): string
    {
        return AbstractTravelItemType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Accommodation::class,
        ]);
    }
}
