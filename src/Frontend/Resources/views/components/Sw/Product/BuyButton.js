class BuyButton extends HeyFrameComponent {

    static selector = 'form[data-component="BuyButton"]';

    static options = {
        redirectSelector: '[name="redirectTo"]',
        redirectParamSelector: '[data-redirect-parameters="true"]',
        redirectTo: 'frontend.cart.offcanvas',
    };

    init() {
        this.redirectInput = this.el.querySelector(this.options.redirectSelector);
        this.redirectParamInput = this.el.querySelector(this.options.redirectParamSelector);

        if (this.redirectInput) {
            this.redirectInput.value = this.options.redirectTo;
        }

        if (this.redirectParamInput) {
            this.redirectParamInput.disabled = true;
        }

        this.el.addEventListener('submit', this.onFormSubmit.bind(this));
    }

    destroy() {
        this.el.removeEventListener('submit', this.onFormSubmit.bind(this));
    }

    onFormSubmit(event) {
        event.preventDefault();

        let requestUrl = this.el.getAttribute('action');
        let formData = window.HeyFrame.serializeForm(this.el);

        ({ requestUrl, formData } = window.HeyFrame.emitInterception(`${this.componentName}:PreSubmit`, { requestUrl, formData }));

        window.HeyFrame.emit(`${this.componentName}:Submit`, requestUrl, formData);

        window.PluginManager.callPluginMethod('OffCanvasCart', 'openOffCanvas', requestUrl, formData);
    }
}

window.HeyFrame.registerComponent('BuyButton', BuyButton);
