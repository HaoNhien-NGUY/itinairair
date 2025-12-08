import { Controller } from '@hotwired/stimulus';
import Sortable from "sortablejs";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['form', 'formInput'];

    static values = {
        primaryTypes: {type: Array, default: []},
        group: String,
        allowPut: {type: Boolean, default: true},
        allowSort: {type: Boolean, default: true},
        toIdeas: {type: Boolean, default: false},
        withHandle: {type: Boolean, default: true},
    };

    initialize() {
        this.dragContainer = document.getElementById('drag-container');
    }

    connect() {
        const options = {
            filter: '.no-drag',
            draggable: '.draggable',
            animation: 250,
            dataIdAttr: 'data-item-id',
            handle: this.withHandleValue ? '.handle' : null,
            sort: this.allowSortValue,
            swapThreshold: 2,
            onStart: (event) => {
                this.dragContainer?.classList.add('is-dragging');
            },
            onEnd: (event) => {
                this.dragContainer?.classList.remove('is-dragging');
            }
        }

        if (this.allowSortValue) {
            options.onSort = (event) => {
                if (event.newIndex === event.oldIndex || event.from !== event.to) return;

                this.persistSort(JSON.stringify(this.sortable.toArray()));
            }
        }

        if (this.allowPutValue) {
            options.onAdd = (event) => {
                const data= this.toIdeasValue ? event.item.dataset.itemId : JSON.stringify(this.sortable.toArray())
                this.persistSort(data);
            }
        }

        if (this.hasGroupValue) {
            options.group = {
                name: this.groupValue,
                put: this.allowPut,
            }
        }

        this.sortable = new Sortable(this.element, options);
    }

    disconnect() {
        this.sortable.destroy();
    }

    persistSort(data) {
        console.log(data);
        this.formInputTarget.value = data;
        this.formTarget.requestSubmit();
    }
}
