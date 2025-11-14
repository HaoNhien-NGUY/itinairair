<?php

namespace App\Twig\Components;

use App\Entity\Activity;
use App\Entity\Place;
use App\Entity\TravelItem;
use App\Enum\TravelItemType;
use App\Form\TravelItem\ActivityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TravelItemForm extends AbstractController
{
    use DefaultActionTrait;
//    use ComponentWithFormTrait;

    #[LiveProp]
    public ?Activity $initialFormData = null;

    #[LiveProp]
    public ?array $placeData = null;

    #[LiveProp]
    public string $formAction;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(ActivityType::class, $this->initialFormData, ['action' => $this->formAction]);
    }

    #[LiveAction]
    public function placeSelected(#[LiveArg] array $placeData): void
    {
        $this->placeData = $placeData;

//        $this->formValues['name'] = $placeData['name'];
//        $this->formValues['place'] = [
//            'name' => $placeData['name'] ?? '',
//            'address' => $placeData['address'] ?? '',
//            'location' => json_encode($placeData['location'] ?? []),
//            'googleMapsURI' => $placeData['googleMapsURI'] ?? '',
//            'photoURI' => $placeData['photoURI'] ?? '',
//            'placeId' => $placeData['placeId'] ?? null,
//            'type' => $placeData['type'] ?? '',
//        ];
    }
}
