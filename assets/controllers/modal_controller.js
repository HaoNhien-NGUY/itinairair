import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ["backdrop", "modal"];

    connect() {
        document.addEventListener('keydown', this.handleEscape.bind(this))
        document.addEventListener("turbo:before-cache", this.cleanFrame.bind(this))
    }

    disconnect() {
        document.removeEventListener('keydown', this.handleEscape.bind(this))
        document.removeEventListener("turbo:before-cache", this.cleanFrame.bind(this))
    }

    backdropTargetConnected(element) {
        if (!this.hasModalTarget || !this.hasBackdropTarget) return;

        document.body.style.overflow = 'hidden';
        window.history.pushState({ modalOpen: true }, "", window.location.href)
        window.addEventListener("popstate", this.handlePopstate)

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

        window.removeEventListener("popstate", this.handlePopstate)

        if (!event || !event.causedByBack) {
            window.history.back()
        }

        event?.preventDefault()
        document.body.style.overflow = '';

        this.backdropTarget.classList.add('opacity-0');
        this.modalTarget.classList.add('translate-y-full', 'opacity-0', 'md:-translate-y-20');

        setTimeout(() => {
            this.cleanFrame();
        }, 500)
    }

    handleEscape(event) {
        if (event.key === 'Escape' && this.hasBackdropTarget) {
            this.close(event)
        }
    }

    handlePopstate = event => {
        event.causedByBack = true;
        this.close(event);
    }

    cleanFrame = () => {
        this.element.innerHTML = '';
        this.element.src = '';
    }
}
