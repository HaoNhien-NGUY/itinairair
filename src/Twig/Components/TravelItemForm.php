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
    use ComponentWithFormTrait;

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
        $lol = [
            "name" => "Sanrio Cafe Ikebukuro-B1 Level Sunshine City",
            "photoURI" => "https://places.googleapis.com/v1/places/ChIJqWqOqFeifDURpYJ5LnxX-Fw/photos/AciIO2dvoJooRHAz95_2qFnq5NGu7EtfRsDZS44DvFF4XIQ2Dv2eKUsXJ8CGHNnG9GWysOcyHayZj1QvV5qFppYsxqRz5MgRv_QqJHuVLB_3vE3_P9wpBkwfpi-3JGmkFOKdGIIn6Iu5rVezGjgIUgX_Q9vKJR1SvDQw-KKHsp-2Ay2A1M9C7T-T9srj_95QR8JEQkviePOfRC53a0P3KRaWX1Oxb4KIT2dw2U61JyuDY-eUGnbJIcSgDp1yTtSOntqL8c2zzlDw5KT_tMwxJupGqLw2mSBvlfmzAuvMBOXby-0gZCWx4R95_Mb0vtGLK70OBZ3uv_uTGWyErCEWSuLheNk-8SXK6FQKPPvoZlEsouK-tn6wIJTTVua79KS_p8SyaVwwGeDsqcYsYPPeoaCy7ttokbaZtdWLSXLcGBc9y-dzcA/media?maxWidthPx=720&key=AIzaSyBDcWHb5qcHDFkMu0DRoW6F3dUDmS3_eNc",
            "address" => "Japon, 〒170-6090 Tokyo, Toshima City, Higashiikebukuro, 1-chōme−28−１ サンシャインシティアルパ B1F",
            "googleMapsURI" => "https://maps.google.com/?cid=6699200636580889253&g_mp=CiVnb29nbGUubWFwcy5wbGFjZXMudjEuUGxhY2VzLkdldFBsYWNlEAIYASAA&hl=en-US&source=apiv3",
            "directionsURI" => "https://www.google.com/maps/dir//''/data=!4m7!4m6!1m1!4e2!1m2!1m1!1s0x357ca257a88e6aa9:0x5cf8577c2e7982a5!3e0?g_mp=CiVnb29nbGUubWFwcy5wbGFjZXMudjEuUGxhY2VzLkdldFBsYWNlEAIYASAA",
            "location" => [
                "lat" => 37.5511694,
                "lng" => 126.9882266,
                ],
            "placeId" => "ChIJqWqOqFeifDURpYJ5LnxX-Fw",
            "type" => "Attraction touristique",
            ];

        $this->placeData = $placeData;
//        $placeData = $lol;

        $this->formValues['name'] = $placeData['name'];
        $this->formValues['place'] = [
            'name' => $placeData['name'] ?? '',
            'address' => $placeData['address'] ?? '',
            'location' => json_encode($placeData['location'] ?? []),
            'googleMapsURI' => $placeData['googleMapsURI'] ?? '',
            'photoURI' => $placeData['photoURI'] ?? '',
            'placeId' => $placeData['placeId'] ?? null,
            'type' => $placeData['type'] ?? '',
        ];
    }
}
