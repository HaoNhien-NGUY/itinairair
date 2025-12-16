import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = [
        'placeInput',
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
        'nameDisplay',
        'photoDisplay',
        'addressDisplay',
        'typeDisplay',
        'placeHolder',
        'placeInfo',
        'addressLinkDisplay',
        'submitButton',
    ];
    static values = {
        primaryTypes: {type: Array, default: []},
        location: {type: Object, default: {}},
    };
    placeAutocomplete = null;

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

        this.placeAutocomplete.Dg?.setAttribute('placeholder', 'Rechercher par nom');
        this.placeAutocomplete.includedPrimaryTypes = this.primaryTypesValue;
        if (Object.keys(this.locationValue).length > 0) {
            this.placeAutocomplete.locationBias = {
                center: this.locationValue,
                radius: 10000,
            };
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
                    'addressComponents',
                    'location',
                    'googleMapsURI',
                    'primaryTypeDisplayName',
                    'googleMapsLinks',
                    'photos',
                ]
            });

            const placeData = this.formatPlaceData(place);
            this.setPlaceComponents(placeData);
        } catch (error) {
            console.error('Error handling place selection:', error);
        }
    }

    setPlaceComponents(placeData) {
        if (this.hasNameTarget) this.nameTarget.value = placeData.name || "";
        if (this.hasAddressTarget) this.addressTarget.value = placeData.address || "";
        if (this.hasCityTarget) this.cityTarget.value = placeData.city || "";
        if (this.hasGoogleMapsURITarget) this.googleMapsURITarget.value = placeData.googleMapsURI || "";
        if (this.hasCountryTarget) this.countryTarget.value = placeData.country || "";
        if (this.hasCountryCodeTarget) this.countryCodeTarget.value = placeData.countryCode || "";
        if (this.hasPlaceIdTarget) this.placeIdTarget.value = placeData.placeId || "";
        if (this.hasTypeTarget) this.typeTarget.value = placeData.type || "";
        if (this.hasLocationTarget) this.locationTarget.value = placeData.location || "";
        if (this.hasPhotoURITarget) this.photoURITarget.value = placeData.photoURI || "";

        if (this.hasNameDisplayTarget) this.nameDisplayTarget.textContent = placeData.name || "";
        if (this.hasPhotoDisplayTarget) this.photoDisplayTarget.src = placeData.photoURI || "";
        if (this.hasAddressDisplayTarget) this.addressDisplayTarget.textContent = placeData.address || "";
        if (this.hasAddressLinkDisplayTarget) this.addressLinkDisplayTarget.href = placeData.googleMapsURI || "";
        if (this.hasTypeDisplayTarget) this.typeDisplayTarget.textContent = placeData.type || "";
        if (this.hasPlaceHolderTarget) this.placeHolderTarget.classList.add('hidden');
        if (this.hasPlaceInfoTarget) this.placeInfoTarget.classList.remove('hidden');
        if (this.hasSubmitButtonTarget) this.submitButtonTarget.disabled = false;
    }

    formatPlaceData = place => {
        const { city, country, countryCode } = this.extractCityAndCountry(place.addressComponents);

        return {
            city,
            country,
            countryCode,
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

    extractCityAndCountry = addressComponents => {
        let locality = null;
        let sublocality = null;
        let adminLevel2 = null;

        const output = {
            city: '',
            country: '',
            countryCode: '',
        }

        for (const component of addressComponents) {
            if (component.types.includes('country')) {
                output.country = component.longText;
                output.countryCode = component.shortText;
                continue;
            }

            if (component.types.includes('locality')) {
                locality = component.longText;
                continue;
            }

            if (component.types.includes('sublocality_level_1')) {
                sublocality = component.longText;
                continue;
            }

            if (component.types.includes('administrative_area_level_2')) {
                adminLevel2 = component.longText;
            }
        }

        output.city = locality || sublocality || adminLevel2 || '';

        return output;
    }
}
