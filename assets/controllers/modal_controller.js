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
            this.backdropTarget.classList.remove('opacity-0');
            this.modalTarget.classList.remove('translate-y-full', 'opacity-0', 'md:-translate-y-20');
        }, 20);
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

        this.backdropTarget.classList.add('opacity-0');
        this.modalTarget.classList.add('translate-y-full', 'opacity-0', 'md:-translate-y-20');

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
