class CDeluxeCallout extends HTMLElement {
    connectedCallback() {
        this.$button = this.querySelector('[data-deluxe-callout-action]') || this.querySelector('[data-callout-action]');
        this.onEnter = () => this.classList.add('is-active');
        this.onLeave = () => this.classList.remove('is-active');

        this.$button?.addEventListener('mouseenter', this.onEnter);
        this.$button?.addEventListener('mouseleave', this.onLeave);
        this.$button?.addEventListener('focus', this.onEnter);
        this.$button?.addEventListener('blur', this.onLeave);
    }

    disconnectedCallback() {
        this.$button?.removeEventListener('mouseenter', this.onEnter);
        this.$button?.removeEventListener('mouseleave', this.onLeave);
        this.$button?.removeEventListener('focus', this.onEnter);
        this.$button?.removeEventListener('blur', this.onLeave);
    }
}

if (!customElements.get('c-deluxe-callout')) {
    customElements.define('c-deluxe-callout', CDeluxeCallout);
}

export default CDeluxeCallout;
