import { Controller } from '@hotwired/stimulus';
import Sortable from "sortablejs";

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['form', 'orderedItemsInput'];

    connect() {
        this.sortable = new Sortable(this.element, {
            filter: '.no-drag',
            draggable: '.draggable',
            animation: 150,
            dataIdAttr: 'data-item-id',
            handle: '.handle',
            onEnd: (event) => {
                if (event.newIndex === event.oldIndex) return;

                this.orderedItemsInputTarget.value = JSON.stringify(this.sortable.toArray());
                this.formTarget.requestSubmit();
            }
        })
    }

    disconnect() {
        this.sortable.destroy();
    }
}
