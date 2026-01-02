<?php

namespace App\Form\TravelItem;

use App\Entity\Note;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteType extends AbstractType
{
    public function __construct(private readonly Security $security) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', HiddenType::class, [
                'empty_data' => null,
            ])
            ->add('name', TextType::class, [
                'label' => 'common.title',
                'empty_data' => '',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: Note 1',
                    'icon' => 'mynaui:tag',
                    'autocomplete' => 'off',
                ],
            ])
            ->remove('status');

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var Note $note */
            $note = $event->getData();

            if (!$note->getName()) $note->setName('');

            /** @var User $user */
            $user = $this->security->getUser();

            if ($user) $note->setAuthor($user);
        });
    }

    public function getParent(): string
    {
        return AbstractTravelItemType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Note::class,
        ]);
    }
}
