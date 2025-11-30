import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

addEventListener("turbo:before-frame-render", (event) => {
    if (document.startViewTransition) {
        const originalRender = event.detail.render;
        event.detail.render = (currentElement, newElement) => {
            document.startViewTransition(() => originalRender(currentElement, newElement));
        };
    }
});
