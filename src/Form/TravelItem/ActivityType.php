<?php

namespace App\Form\TravelItem;

use App\Entity\Activity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Activity>
 */
class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', HiddenType::class, [
                'empty_data' => null,
            ])
            ->add('place', PlaceType::class, [
                'required' => false,
                'label'    => false,
            ])
            ->add('startTime', TimeType::class, [
                'label' => 'Heure de debut',
                'input'  => 'datetime',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-input py-1.5'],
            ])
            ->add('endTime', TimeType::class, [
                'label' => 'Heure de fin',
                'input'  => 'datetime',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-input py-1.5'],
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Activity $activity */
            $activity = $event->getData();
            $activity->setName($activity->getPlace()->getName());
        });
    }

    public function getParent(): string
    {
        return AbstractTravelItemType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
