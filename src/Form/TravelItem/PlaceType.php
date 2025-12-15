<?php


namespace App\Form\TravelItem;

use App\Entity\Place;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PlaceType extends AbstractType
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fields = [
            'name',
            'address',
            'city',
            'country',
            'countryCode',
            'location',
            'googleMapsURI',
            'photoURI',
            'placeId',
            'type',
        ];

        foreach ($fields as $field) {
            $builder->add($field, HiddenType::class, [
                'attr' => [
                    'data-google-place-widget-target' => $field,
                ],
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (empty($data['photoURI'])) {
                return;
            }

            try {
                $response = $this->httpClient->request('GET', $data['photoURI'], [
                    'max_redirects' => 0,
                ]);
                $headers = $response->getHeaders(false);

                if (isset($headers['location'][0])) {
                    $data['photoURI'] = $headers['location'][0];
                    $event->setData($data);
                }
            } catch (TransformationFailedException $e) {
                return;
            };
        });

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
