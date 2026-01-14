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

        this.element.addEventListener('wheel', this.handleScroll);
    }

    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }

        this.element.removeEventListener('wheel', this.handleScroll);
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

    handleScroll = (event) => {
        if (event.deltaY !== 0) {
            event.preventDefault();
            this.element.scrollLeft += event.deltaY;
        }
    };

    scrollToCenter(target) {
        const container = this.element;

        container.scrollTo({
            left: target.offsetLeft - (container.clientWidth / 3) + (target.clientWidth / 2),
            behavior: 'smooth'
        });
    }
}
