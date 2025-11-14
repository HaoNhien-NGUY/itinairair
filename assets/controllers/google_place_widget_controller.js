// assets/controllers/google_place_widget_controller.js

import { Controller } from "@hotwired/stimulus";
import { getComponent } from "@symfony/ux-live-component";

export default class extends Controller {
    static targets = [
        'placeInput',
        'name',
        'address',
        'location',
        'googleMapsURI',
        'photoURI',
        'placeId',
        'type',
    ];
    placeAutocomplete = null;

    // async connect() {
    //     try {
    //         this.component = await getComponent(this.element);
    //     } catch (error) {
    //         console.log('FUCK');
    //         console.error('Error initializing live component:', error);
    //     }
    //     this.initializeGooglePlaces();
    //
    // }

    connect() {
        this.initializeGooglePlaces();
    }

    disconnect() {
        this.placeAutocomplete.removeEventListener('gmp-select', this.handleSelection);
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

            const placeData = this.formatPlaceData(place);
            this.setPlaceComponents(placeData);

            // this.component.action('placeSelected', { placeData });

        } catch (error) {
            console.error('Error handling place selection:', error);
        }
    }

    setPlaceComponents(placeData) {
        if (this.hasNameTarget) this.nameTarget.value = placeData.name || ""
        if (this.hasAddressTarget) this.addressTarget.value = placeData.address || ""
        if (this.hasCityTarget) this.cityTarget.value = placeData.locality || ""
        if (this.hasGoogleMapsURITarget) this.googleMapsURITarget.value = placeData.googleMapsURI || ""
        if (this.hasCountryTarget) this.countryTarget.value = placeData.country || ""
        if (this.hasPostalCodeTarget) this.postalCodeTarget.value = placeData.postal_code || ""
        if (this.hasPlaceIdTarget) this.placeIdTarget.value = placeData.placeId || ""
        if (this.hasTypeTarget) this.typeTarget.value = placeData.type || ""
        if (this.hasLocationTarget) this.locationTarget.value = placeData.location || ""
        if (this.hasPhotoURITarget) this.photoURITarget.value = placeData.photoURI || ""
    }

    formatPlaceData = place => {
        return {
            name: place.displayName,
            photoURI: place.photos[0]?.getURI({maxHeight: 600, maxWidth: 600}) ?? null,
            address: place.formattedAddress,
            googleMapsURI: place.googleMapsURI,
            directionsURI: place.googleMapsLinks.directionsURI,
            location: JSON.stringify(place.location),
            placeId: place.id,
            type: place.primaryTypeDisplayName || '',
        };
    }
}
