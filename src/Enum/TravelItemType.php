<?php


namespace App\Enum;

use App\Entity\Accommodation;
use App\Entity\Activity;
use App\Entity\Destination;
use App\Entity\Flight;
use App\Entity\Note;
use App\Entity\TravelItem;
use App\Form\TravelItem\AccommodationType;
use App\Form\TravelItem\ActivityType;
use App\Form\TravelItem\DestinationType;
use App\Form\TravelItem\FlightType;
use App\Form\TravelItem\NoteType;

enum TravelItemType: string
{
    case ACCOMMODATION = 'accommodation';
    case FLIGHT = 'flight';
    case ACTIVITY = 'activity';
    case DESTINATION = 'destination';
    case NOTE = 'note';

    public function getFormType(): string
    {
        return match ($this) {
            self::ACCOMMODATION => AccommodationType::class,
            self::FLIGHT => FlightType::class,
            self::ACTIVITY => ActivityType::class,
            self::DESTINATION => DestinationType::class,
            self::NOTE => NoteType::class,
        };
    }

    public function getClass(): string
    {
        return match($this) {
            self::ACCOMMODATION => Accommodation::class,
            self::FLIGHT => Flight::class,
            self::ACTIVITY => Activity::class,
            self::DESTINATION => Destination::class,
            self::NOTE => Note::class,
        };
    }

    public function createInstance(array $params = []): TravelItem
    {
        return new ($this->getClass())(...$params);
    }

    public function getFormTemplate(): string
    {
        return match($this) {
            self::FLIGHT => 'travel_item/flight/_create_modal.frame.html.twig',
            self::ACCOMMODATION => 'travel_item/accommodation/_create_modal.frame.html.twig',
            self::ACTIVITY => 'travel_item/activity/_create_modal.frame.html.twig',
            self::DESTINATION => 'travel_item/destination/_create_modal.frame.html.twig',
            self::NOTE => 'travel_item/note/_create_modal.frame.html.twig',
        };
    }

    public function isPositionable(): bool
    {
        return match ($this) {
            self::ACTIVITY, self::NOTE => true,
            default => false,
        };
    }

    public static function fromClass(string $className): self
    {
        return match ($className) {
            Accommodation::class => self::ACCOMMODATION,
            Flight::class => self::FLIGHT,
            Activity::class => self::ACTIVITY,
            Destination::class => self::DESTINATION,
            Note::class => self::NOTE,
            default => throw new \InvalidArgumentException("Unknown TravelItem class: $className"),
        };
    }
}
