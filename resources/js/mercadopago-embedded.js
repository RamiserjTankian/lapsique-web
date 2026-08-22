const SDK_URL = 'https://sdk.mercadopago.com/js/v2';

const copy = () => document.documentElement.lang === 'en' ? {
    loading: 'Loading Mercado Pago secure form…',
    submitting: 'Processing payment. Keep this page open.',
    rejected: 'Payment was rejected. Check your card details and try again.',
    received: 'Payment received. Your tickets will be issued after Mercado Pago verifies it.',
    sendError: 'Unable to start payment. Try again.',
    loadError: 'Card payment is unavailable right now.',
} : {
    loading: 'Cargando el formulario seguro de Mercado Pago…',
    submitting: 'Procesando el pago. Mantén esta página abierta.',
    rejected: 'El pago fue rechazado. Revisa los datos de tu tarjeta e inténtalo de nuevo.',
    received: 'Pago recibido. Tus accesos se emitirán cuando Mercado Pago lo verifique.',
    sendError: 'No fue posible iniciar el pago. Inténtalo de nuevo.',
    loadError: 'El pago con tarjeta no está disponible en este momento.',
};

const state = (element, value, message = '') => {
    const surface = element.closest('[data-payment-state]') || element.parentElement;

    if (surface) {
        surface.dataset.paymentState = value;
        surface.setAttribute('aria-busy', ['loading', 'submitting', 'submitted'].includes(value) ? 'true' : 'false');
        const status = surface.querySelector('[data-mercadopago-status]');
        if (status instanceof HTMLElement) status.textContent = message;
    }

    window.dispatchEvent(new CustomEvent('mercadopago:payment-state', {
        detail: message ? { state: value, message } : { state: value },
    }));
};

const loadSdk = (url) => new Promise((resolve, reject) => {
    if (window.MercadoPago) {
        resolve();
        return;
    }

    const existing = document.querySelector('script[data-mercadopago-sdk]');
    if (existing) {
        existing.addEventListener('load', resolve, { once: true });
        existing.addEventListener('error', reject, { once: true });
        return;
    }

    const script = document.createElement('script');
    script.src = url === SDK_URL ? url : SDK_URL;
    script.async = true;
    script.dataset.mercadopagoSdk = 'true';
    script.addEventListener('load', resolve, { once: true });
    script.addEventListener('error', reject, { once: true });
    document.head.appendChild(script);
});

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const withTimeout = (promise, milliseconds) => Promise.race([
    promise,
    new Promise((_, reject) => window.setTimeout(() => reject(new Error('mercadopago_timeout')), milliseconds)),
]);

const configuration = async (element) => {
    const endpoint = element.dataset.mercadopagoConfigurationUrl;
    if (!endpoint) throw new Error('configuration_missing');

    const response = await fetch(endpoint, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    if (!response.ok) throw new Error('configuration_unavailable');
    return response.json();
};

const paymentData = (formData) => ({
    token: formData.token,
    payment_method_id: formData.payment_method_id,
    issuer_id: formData.issuer_id || undefined,
    installments: formData.installments,
    payer: formData.payer?.identification
        ? { identification: {
            type: formData.payer.identification.type,
            number: formData.payer.identification.number,
        } }
        : undefined,
});

const postPayment = async (url, formData) => {
    const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(paymentData(formData)),
    });

    if (!response.ok) throw new Error('payment_unavailable');
    return response.json();
};

const mount = async (element) => {
    if (!(element instanceof HTMLElement) || element.dataset.mercadopagoMounted === 'true') return;

    const words = copy();
    element.dataset.mercadopagoMounted = 'true';
    state(element, 'loading', words.loading);

    try {
        const config = await configuration(element);
        if (!config.public_key || !config.payment_url || !Number.isFinite(Number(config.amount)) || Number(config.amount) <= 0) {
            throw new Error('configuration_invalid');
        }

        await loadSdk(config.sdk_url || SDK_URL);
        const mercadoPago = new window.MercadoPago(config.public_key, {
            locale: document.documentElement.lang === 'en' ? 'en-US' : 'es-MX',
        });
        const bricksBuilder = mercadoPago.bricks();
        let ready = false;
        const readinessTimeout = window.setTimeout(() => {
            if (ready) return;
            element.dataset.mercadopagoMounted = 'false';
            state(element, 'error', words.loadError);
        }, 15000);

        element.replaceChildren();
        await withTimeout(Promise.resolve(bricksBuilder.create('cardPayment', element.id, {
            initialization: { amount: Number(config.amount) },
            customization: {
                visual: {
                    style: {
                        theme: 'flat',
                        customVariables: {
                            textPrimaryColor: '#151713',
                            textSecondaryColor: '#565852',
                            inputBackgroundColor: '#ffffff',
                            formBackgroundColor: '#ffffff',
                            baseColor: '#e56510',
                            baseColorFirstVariant: '#bd4d05',
                            outlinePrimaryColor: '#151713',
                            outlineSecondaryColor: '#c7c5bd',
                            buttonTextColor: '#ffffff',
                            borderRadiusSmall: '0px',
                            borderRadiusMedium: '0px',
                            borderRadiusLarge: '0px',
                            fontWeightSemiBold: '700',
                        },
                    },
                },
            },
            callbacks: {
                onReady: () => {
                    ready = true;
                    window.clearTimeout(readinessTimeout);
                    state(element, 'ready');
                },
                onSubmit: async (formData) => {
                    state(element, 'submitting', words.submitting);

                    try {
                        const payment = await postPayment(config.payment_url, formData);
                        const rejected = payment.status === 'rejected';
                        const message = rejected ? words.rejected : words.received;
                        state(element, rejected ? 'error' : 'submitted', message);

                        if (config.meta_event_id) {
                            window.trackMetaPixel?.('AddPaymentInfo', {
                                content_type: 'product',
                                currency: config.currency,
                                value: Number(config.amount),
                            }, { eventID: config.meta_event_id });
                        }

                        if (!rejected) {
                            window.dispatchEvent(new CustomEvent('mercadopago:payment-submitted', {
                                detail: { status: payment.status, resultUrl: config.result_url || '' },
                            }));
                        }
                    } catch (_) {
                        state(element, 'error', words.sendError);
                    }
                },
                onError: () => state(element, 'error', words.loadError),
            },
        })), 15000);
    } catch (_) {
        element.dataset.mercadopagoMounted = 'false';
        state(element, 'error', words.loadError);
    }
};

window.MercadoPagoEmbedded = Object.freeze({ mount });

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-mercadopago-configuration-url]').forEach((element) => void mount(element));
});
