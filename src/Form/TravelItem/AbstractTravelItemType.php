<?php


namespace App\Form\TravelItem;

use App\Entity\TravelItem;
use App\Entity\Trip;
use App\Enum\ItemStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AbstractTravelItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('notes', TextareaType::class, [
                'label' => 'form.label.notes',
                'required' => false,
                'attr' => [
                    'placeholder' => 'form.placeholder.notes',
                    'rows' => 1,
                    'icon' => 'subway:paragraph-2',
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 1000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('status', EnumType::class, [
                'class' => ItemStatus::class,
                'required' => false,
                'placeholder' => null,
//                'label' => 'form.label.status',
                'attr' => ['icon' => 'mdi:list-status'],
            ]);


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var TravelItem $entity */
            $entity = $event->getData();
            $form = $event->getForm();

            // when created
            if ($entity && null === $entity->getId() && $entity->getStatus() === ItemStatus::IDEA) {
                $form->remove('status');
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
        $resolver->setRequired('trip');
        $resolver->setAllowedTypes('trip', Trip::class);

    }
}
