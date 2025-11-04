import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["backdrop", "modal"];

    connect() {
        document.addEventListener('keydown', this.handleEscape.bind(this))
    }

    disconnect() {
        document.removeEventListener('keydown', this.handleEscape.bind(this))
    }

    backdropTargetConnected(element) {
        if (!this.hasModalTarget || !this.hasBackdropTarget) return;

        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            element.style.opacity = '1';
            this.modalTarget.style.transform = 'translateY(-20%)';
        }, 0);

        setTimeout(() => {
            this.modalTarget.style.opacity = '1';
        }, 300);
    }

    closeTargetConnected() {
        this.close();
    }

    handleBackdropClick(event) {
        if (event.target === event.currentTarget) {
            this.close(event)
        }
    }

    close(event = null) {
        if (!this.hasModalTarget || !this.hasBackdropTarget) return;

        event?.preventDefault()
        document.body.style.overflow = '';
        this.modalTarget.style.transform = 'translateY(-35%)';
        this.modalTarget.style.opacity = '0';

        setTimeout(() => {
            this.backdropTarget.style.opacity = '0'
        }, 200)

        setTimeout(() => {
            this.element.innerHTML = '';
        }, 500)
    }

    handleEscape(event) {
        if (event.key === 'Escape' && this.hasBackdropTarget) {
            this.close(event)
        }
    }
}
