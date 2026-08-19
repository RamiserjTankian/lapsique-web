const SDK_URL = 'https://sdk.mercadopago.com/js/v2';

const setState = (element, value, message) => {
    const surface = element.closest('[data-payment-state]') || element.parentElement;
    if (surface) surface.dataset.paymentState = value;

    const status = document.getElementById('mercadopago-card-form-status');
    if (status && message) status.textContent = message;
};

const loadSdk = (url) => new Promise((resolve, reject) => {
    if (window.MercadoPago) return resolve();

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

const fetchConfiguration = async (element) => {
    const endpoint = element.dataset.mercadopagoConfigurationUrl;
    if (!endpoint) throw new Error('configuration_missing');

    const response = await fetch(endpoint, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    if (!response.ok) throw new Error('configuration_unavailable');
    return response.json();
};

const safePaymentData = (formData) => ({
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
        body: JSON.stringify(safePaymentData(formData)),
    });

    if (!response.ok) throw new Error('payment_unavailable');
    return response.json();
};

const mount = async (element) => {
    setState(element, 'loading');

    try {
        const config = await fetchConfiguration(element);
        if (!config.public_key?.startsWith('TEST-') || !config.payment_url || Number(config.amount) <= 0) {
            throw new Error('configuration_invalid');
        }

        await loadSdk(config.sdk_url || SDK_URL);
        const mercadoPago = new window.MercadoPago(config.public_key, { locale: document.documentElement.lang === 'en' ? 'en-US' : 'es-MX' });
        const bricksBuilder = mercadoPago.bricks();

        element.replaceChildren();
        await bricksBuilder.create('cardPayment', element.id, {
            initialization: { amount: Number(config.amount) },
            callbacks: {
                onReady: () => setState(element, 'ready', document.documentElement.lang === 'en' ? 'Secure form ready.' : 'Formulario seguro listo.'),
                onSubmit: async (formData) => {
                    setState(element, 'loading', document.documentElement.lang === 'en' ? 'Sending test payment…' : 'Enviando pago de prueba…');

                    try {
                        const payment = await postPayment(config.payment_url, formData);
                        const rejected = payment.status === 'rejected';
                        setState(
                            element,
                            rejected ? 'error' : 'ready',
                            rejected
                                ? (document.documentElement.lang === 'en' ? 'Payment rejected. Check the test card and try again.' : 'Pago rechazado. Revisa la tarjeta de prueba e inténtalo de nuevo.')
                                : (document.documentElement.lang === 'en' ? 'Payment received. Waiting for verified confirmation…' : 'Pago recibido. Esperando confirmación verificada…'),
                        );

                        if (config.meta_event_id) {
                            window.trackMetaPixel?.('AddPaymentInfo', {
                                content_type: 'product',
                                currency: config.currency,
                                value: Number(config.amount),
                            }, { eventID: config.meta_event_id });
                        }

                        if (!rejected && config.result_url) {
                            window.setTimeout(() => window.location.assign(config.result_url), 900);
                        }
                    } catch (_) {
                        setState(element, 'error', document.documentElement.lang === 'en' ? 'Unable to send the test payment. Try again.' : 'No fue posible enviar el pago de prueba. Inténtalo de nuevo.');
                    }
                },
                onError: () => setState(element, 'error', document.documentElement.lang === 'en' ? 'Unable to load the secure payment form.' : 'No fue posible cargar el formulario seguro.'),
            },
        });
    } catch (_) {
        setState(element, 'error', document.documentElement.lang === 'en' ? 'Test payment is not configured yet.' : 'El pago de prueba aún no está configurado.');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#mercadopago-card-form').forEach((element) => void mount(element));
});
