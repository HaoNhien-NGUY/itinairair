<?php


namespace App\Form\TravelItem;

use App\Entity\Accommodation;
use App\Entity\Activity;
use App\Entity\Day;
use App\Entity\Flight;
use App\Entity\Place;
use App\Enum\ItemStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PlaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',HiddenType::class)
            ->add('address', HiddenType::class)
            ->add('location', HiddenType::class)
            ->add('googleMapsURI', HiddenType::class)
            ->add('photoURI', HiddenType::class)
            ->add('placeId', HiddenType::class)
            ->add('type', HiddenType::class);

        $builder->get('location')
            ->addModelTransformer(new CallbackTransformer(
                function ($value): string {
                    return $value ? json_encode($value) : '';
                },
                function ($value): array {
                    return !empty($value) ? json_decode($value, true) : [];
                }
            ));

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
        ]);
    }
}
