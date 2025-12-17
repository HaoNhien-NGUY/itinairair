import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['item']

    connect() {
        this.class = this.hasHiddenClass ? this.hiddenClass : "hidden"
    }

    toggle() {
        this.itemTargets.forEach((item) => {
            item.classList.toggle(this.class)
        })
    }

    show() {
        this.itemTargets.forEach((item) => {
            item.classList.remove(this.class)
        })
    }

    hide() {
        this.itemTargets.forEach((item) => {
            item.classList.add(this.class)
        })
    }
}
