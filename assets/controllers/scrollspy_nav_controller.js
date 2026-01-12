import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        activeClass: { type: String, default: 'active' },
    }

    connect() {
        this.observer = new MutationObserver(this.handleMutations.bind(this));

        this.observer.observe(this.element, {
            attributes: true,
            subtree: true,
            attributeFilter: ['class']
        });
    }

    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }
    }

    handleMutations(mutations) {
        for (const mutation of mutations) {
            const target = mutation.target;

            if (target instanceof Element &&
                this.element.contains(target) &&
                target.classList.contains(this.activeClassValue)) {

                this.scrollToCenter(target);
                break;
            }
        }
    }

    scrollToCenter(target) {
        const container = this.element;

        container.scrollTo({
            left: target.offsetLeft,
            behavior: 'smooth'
        });
    }
}
