import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['backdrop']
    static values = {
        hiddenClass: {type: String, default: 'translate-x-full'},
    }
    open() {
        this.backdropTarget.classList.remove(this.hiddenClassValue);
        document.body.classList.add('overflow-hidden');
    }

    close() {
        this.backdropTarget.classList.add(this.hiddenClassValue);
    }
}
