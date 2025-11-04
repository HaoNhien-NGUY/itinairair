// assets/controllers/google_place_widget_controller.js

import { Controller } from "@hotwired/stimulus";
import { getComponent } from "@symfony/ux-live-component";

export default class extends Controller {
    static targets = ["placeInput"];
    placeAutocomplete = null;

    async initialize() {
        this.component = await getComponent(this.element);
    }

    connect() {
        this.initializeGooglePlaces();
    }

    disconnect() {
        this.placeAutocomplete.removeEventListener('gmp-select', this.handleSelection);
    }

    testaction() {
        this.component.action('placeSelected', { placeData: {} });
    }

    initializeGooglePlaces() {
        if (typeof google === 'undefined' || !google.maps) {
            console.error('Google Maps JavaScript API not loaded');
            return;
        }

        this.placeAutocomplete = this.placeInputTarget;

        if (!this.placeAutocomplete) {
            console.error('Place autocomplete element not found');
            return;
        }

        this.placeAutocomplete.addEventListener('gmp-select', this.handleSelection);
    }

    handleSelection = async ({ placePrediction })=> {
        try {
            const place = placePrediction.toPlace();

            await place.fetchFields({
                fields: [
                    'displayName',
                    'formattedAddress',
                    'location',
                    'googleMapsURI',
                    'primaryTypeDisplayName',
                    'googleMapsLinks',
                    'photos',
                ]
            });

            console.log(place.googleMapsLinks, place.googleMapsLinks.directionsURI);
            const placeData = this.formatPlaceData(place);

            this.component.action('placeSelected', { placeData });

        } catch (error) {
            console.error('Error handling place selection:', error);
        }
    }

    formatPlaceData = place => {
        const photoURI = place.photos[0]?.getURI({maxHeight: 400}) || '';

        place.photos.forEach((photo, i) => {
            console.log('photo'+ i + photo.getURI({maxHeight: 400}));
        })

        return {
            name: place.displayName,
            photoURI: photoURI,
            address: place.formattedAddress,
            googleMapsURI: place.googleMapsURI,
            directionsURI: place.googleMapsLinks.directionsURI,
            location: place.location.toJSON(),
            placeId: place.id,
            type: place.primaryTypeDisplayName || '',
        };
    }
}
