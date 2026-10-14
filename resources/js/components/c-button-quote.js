// Resting position of the cursor-following face inside the button icon (view-model units)
const REST = { x: -34, y: -15 };
const EASE = 0.1;

/**
 * <c-button-quote>: the nav CTA. Its icon is a Rive character (state machine "State Machine 1")
 * whose eyes follow the mouse through the view-model numbers posx / posy, and which smiles
 * (input isSmiling) while the button is hovered. When the pointer leaves the page it eases back to rest.
 */
class CButtonQuote extends HTMLElement {
    connectedCallback() {
        this.$canvas = this.querySelector('.c-button-quote_icon_canvas');
        this.$button = this.querySelector('.c-button-quote');
        this.frame = null;
        if (this.$canvas) this.initRive();
    }

    disconnectedCallback() {
        if (this.frame !== null) cancelAnimationFrame(this.frame);
        window.removeEventListener('mousemove', this.onMouseMove);
        this.rive?.cleanup();
    }

    async initRive() {
        const { Rive } = await import('../rive.js');
        if (!this.isConnected) return;
        this.rive = new Rive({
            src: '/rive/mindmarket_cursor_crop.riv',
            canvas: this.$canvas,
            stateMachines: 'State Machine 1',
            autoplay: true,
            autoBind: true,
            onLoad: () => {
                this.rive.resizeDrawingSurfaceToCanvas();
                this.smiling = this.rive.stateMachineInputs('State Machine 1')?.find((i) => i.name === 'isSmiling');
                const vm = this.rive.viewModelInstance;
                if (!vm) return;
                this.posX = vm.number('posx');
                this.posY = vm.number('posy');
                if (this.posX) this.posX.value = REST.x;
                if (this.posY) this.posY.value = REST.y;

                window.addEventListener('mousemove', this.onMouseMove);
                this.$button.addEventListener('mouseenter', () => this.smiling && (this.smiling.value = true));
                this.$button.addEventListener('mouseleave', () => this.smiling && (this.smiling.value = false));
                document.addEventListener('mouseleave', () => this.easeToRest());
            },
        });
    }

    // Maps the pointer offset inside the canvas box to the view-model range (-90 to 20)
    toRange = (offset, size) => -90 + (offset / size) * 110;

    onMouseMove = (e) => {
        if (this.frame !== null) { cancelAnimationFrame(this.frame); this.frame = null; }
        const box = this.$canvas.getBoundingClientRect();
        if (this.posX) this.posX.value = this.toRange(e.clientX - box.left, box.width);
        if (this.posY) this.posY.value = this.toRange(e.clientY - box.top, box.height);
    };

    easeToRest = () => {
        if (!this.posX || !this.posY) return;
        const { value: x } = this.posX;
        const { value: y } = this.posY;
        if (Math.abs(x - REST.x) < 0.5 && Math.abs(y - REST.y) < 0.5) {
            this.posX.value = REST.x;
            this.posY.value = REST.y;
            this.frame = null;
            return;
        }
        this.posX.value = x + (REST.x - x) * EASE;
        this.posY.value = y + (REST.y - y) * EASE;
        this.frame = requestAnimationFrame(this.easeToRest);
    };
}

if (!customElements.get('c-button-quote')) {
    customElements.define('c-button-quote', CButtonQuote);
}

export default CButtonQuote;
