<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'attr' => ['class' => 'form-input bg-indigo-10 px-4 py-2'],
                'label' => 'common.username',
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'form.user_account.bio',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'form.user_account.bio_placeholder'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'common.modify',
                'attr' => [
                    'class' => 'block w-full py-2'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
