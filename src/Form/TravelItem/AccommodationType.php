<?php


namespace App\Form\TravelItem;

use App\Entity\Accommodation;
use App\Entity\Day;
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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AccommodationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Trip $trip */
        $trip = $options['trip'];

        $builder
            ->add('name', TextType::class)
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
            ]);

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
