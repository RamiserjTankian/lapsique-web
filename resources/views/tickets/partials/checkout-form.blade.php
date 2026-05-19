@php
    $currency = $products->first()?->currency ?? config('mercadopago.currency', 'MXN');
    $defaultProvider = old('payment_provider', $defaultPaymentProvider ?? 'mercadopago');
    $openModal = $errors->has('buyer_name')
        || $errors->has('buyer_email')
        || $errors->has('buyer_whatsapp')
        || $errors->has('buyer_instagram')
        || $errors->has('payment_provider');

    $preselectedItems = request()->query('items');
    if (!is_array($preselectedItems)) {
        $preselectedItems = [];
    }
    $preselectedItems = array_map('intval', $preselectedItems);
    $hasPreselected = collect($preselectedItems)->sum() > 0;
    $openModal = $openModal || $hasPreselected;

    $categoryLabels = [
        'ticket'    => 'Acceso General',
        'consumo'   => 'Consumo mínimo',
        'table'     => 'Mesas',
        'combo'     => 'Combos',
        'multipass' => 'MultiPass',
    ];
    $categoryOrder = ['ticket', 'consumo', 'table', 'combo', 'multipass'];
    $grouped = $products->groupBy('category');
    $orderedCategories = $categoryOrder;
    $extraCategories = $grouped->keys()->diff($orderedCategories)->values()->all();
@endphp

<form
    action="{{ route('tickets.checkout.store', $event) }}"
    method="POST"
    class="space-y-6"
    data-ticket-checkout
    data-ticket-open-on-load="{{ $openModal ? '1' : '0' }}"
>
    @csrf
    <input type="hidden" name="invite_token" value="{{ $inviteToken }}">

    {{-- Info de política --}}
    <div class="rounded-xl border border-amber-400/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200 space-y-1">
        <p class="font-semibold">📋 Información importante</p>
        <ul class="list-disc list-inside space-y-0.5 text-amber-100/90">
            <li>Acceso únicamente mediante reservación anticipada.</li>
            <li>El monto pagado se convierte en <strong>crédito para consumo</strong> de alimentos y bebidas dentro del evento.</li>
            <li>Se aplica un <strong>cargo de servicio del 15%</strong> sobre el consumo mínimo.</li>
            <li>Las reservaciones no son reembolsables.</li>
            <li>Capacidad limitada.</li>
        </ul>
    </div>

    <div class="space-y-8">
        @foreach (array_merge($orderedCategories, $extraCategories) as $cat)
            @if (!isset($grouped[$cat]) || $grouped[$cat]->isEmpty())
                @continue
            @endif
            @php
                $items = $grouped[$cat];
                $sectionTitle = $categoryLabels[$cat] ?? ucfirst($cat);
            @endphp
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">{{ $sectionTitle }}</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach ($items as $product)
                        @php
                            $available       = $product->availableStock();
                            $hasCharge       = $product->service_charge_pct > 0;
                            $basePrice       = $hasCharge ? round((float)$product->price / (1 + $product->service_charge_pct / 100), 2) : (float)$product->price;
                            $chargeAmount    = round((float)$product->price - $basePrice, 2);
                        @endphp
                        <div class="card p-5 space-y-3" data-product-card="{{ $product->id }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-lg font-semibold text-white">{{ $product->name }}</h4>
                                    @if ($product->description)
                                        <p class="text-sm text-gray-400 mt-1">{{ $product->description }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Desglose de precio --}}
                            <div class="rounded-lg bg-white/5 px-3 py-2 space-y-1 text-sm">
                                @if ($hasCharge)
                                    <div class="flex justify-between text-gray-300">
                                        <span>Consumo mínimo</span>
                                        <span>{{ number_format($basePrice, 0) }} {{ $product->currency }}</span>
                                    </div>
                                    <div class="flex justify-between text-gray-400">
                                        <span>Cargo de servicio ({{ number_format($product->service_charge_pct, 0) }}%)</span>
                                        <span>{{ number_format($chargeAmount, 0) }} {{ $product->currency }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold text-white border-t border-white/10 pt-1">
                                        <span>Total</span>
                                        <span>{{ number_format((float)$product->price, 0) }} {{ $product->currency }}</span>
                                    </div>
                                @else
                                    <div class="flex justify-between font-semibold text-white">
                                        <span>Precio</span>
                                        <span>{{ number_format((float)$product->price, 0) }} {{ $product->currency }}</span>
                                    </div>
                                @endif
                                @if ($product->access_units > 1)
                                    <p class="text-xs text-gray-500 pt-0.5">Incluye acceso para {{ $product->access_units }} personas.</p>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-4">
                                @if ($available !== null && $available === 0)
                                    <span class="text-sm text-red-400 font-medium">Agotado</span>
                                @else
                                    <label class="text-sm text-gray-300">Cantidad</label>
                                    <input
                                        type="number"
                                        min="0"
                                        @if ($available !== null) max="{{ $available }}" @endif
                                        name="items[{{ $product->id }}]"
                                        value="{{ (int) ($preselectedItems[$product->id] ?? 0) }}"
                                        class="field w-24"
                                        data-ticket-price="{{ $product->price }}"
                                        data-ticket-name="{{ $product->name }}"
                                        data-ticket-id="{{ $product->id }}"
                                    >
                                    @if ($product->max_per_order)
                                        <span class="text-xs text-gray-500">Máx. {{ $product->max_per_order }}</span>
                                    @endif
                                    @if ($available !== null)
                                        <span class="text-xs text-gray-500">{{ $available }} disponibles</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @if ($products->isEmpty())
        <div class="card p-6">
            <p class="text-gray-300">No hay opciones de acceso disponibles para este evento.</p>
        </div>
    @endif

    <div class="card p-5 space-y-4">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-white">Resumen</h3>
                <p class="text-sm text-gray-400">Completa los datos en el siguiente paso.</p>
            </div>
            <div class="text-sm text-gray-300 whitespace-pre-line" data-ticket-summary>
                Selecciona una opción para ver el total.
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="text-lg font-semibold text-white">
                Total: <span data-ticket-total>0.00 {{ $currency }}</span>
            </div>
            <button
                type="button"
                class="btn btn-primary px-8 disabled:opacity-50 disabled:cursor-not-allowed"
                data-ticket-open
            >
                Continuar y pagar
            </button>
        </div>
    </div>

    {{-- Modal de datos del comprador --}}
    <div class="fixed inset-0 z-50 hidden flex items-center justify-center px-4 py-6" data-ticket-modal>
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" data-ticket-close></div>
        <div class="relative w-full max-w-2xl rounded-[28px] border border-[#DED2BB] bg-[#FDFAF6] p-6 shadow-[0_24px_80px_rgba(7,30,42,0.22)] overflow-y-auto max-h-[90vh]">
            <button type="button" class="absolute right-4 top-4 text-[#6B7F8E] hover:text-[#1A2D3D]" data-ticket-close>
                ✕
            </button>
            <div class="space-y-1">
                <p class="pill border-[#7BAFC4] text-[#0B3749] bg-[#EEF7FB]">Datos del comprador</p>
                <h3 class="text-2xl font-semibold text-[#1A2D3D]">Confirma tu reservación</h3>
                <p class="text-sm text-[#5D7282]">Recibirás tu QR de acceso por correo al completar el pago.</p>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div class="space-y-3">
                    <input type="text"  name="buyer_name"      placeholder="Nombre completo *"  class="field" value="{{ old('buyer_name', request('buyer_name')) }}"      required>
                    <input type="email" name="buyer_email"     placeholder="Email *"             class="field" value="{{ old('buyer_email', request('buyer_email')) }}"     required>
                    <input type="tel"   name="buyer_whatsapp"  placeholder="Teléfono / WhatsApp *" class="field" value="{{ old('buyer_whatsapp', request('buyer_whatsapp')) }}"  required>
                    <input type="text"  name="buyer_instagram" placeholder="Instagram @usuario (opcional)"  class="field" value="{{ old('buyer_instagram', request('buyer_instagram', '')) }}">
                </div>
                <div class="space-y-4">
                    <div class="rounded-2xl border border-[#DED2BB] bg-white p-4 space-y-2 shadow-sm">
                        <p class="text-sm font-medium text-[#5D7282]">Resumen de tu orden</p>
                        <div class="text-sm text-[#3D5066] whitespace-pre-line" data-ticket-summary>
                            Selecciona una opción para ver el total.
                        </div>
                        <div class="text-lg font-semibold text-[#1A2D3D] border-t border-[#E9DFCF] pt-2">
                            Total: <span data-ticket-total>0.00 {{ $currency }}</span>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-[#DED2BB] bg-white p-4 space-y-3 shadow-sm">
                        <h4 class="text-sm font-semibold text-[#1A2D3D]">Método de pago</h4>
                        <div class="space-y-2 text-sm text-[#3D5066]">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="payment_provider" value="mercadopago" {{ $defaultProvider === 'mercadopago' ? 'checked' : '' }} required>
                                <span>MercadoPago</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="payment_provider" value="stripe" {{ $defaultProvider === 'stripe' ? 'checked' : '' }}>
                                <span>Stripe / Tarjeta</span>
                            </label>
                        </div>
                    </div>
                    <p class="text-xs text-[#6B7F8E] leading-relaxed">
                        Al confirmar aceptas que el monto pagado se aplicará como crédito de consumo dentro del evento. Las reservaciones no son reembolsables.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                <button type="button" class="btn btn-ghost" data-ticket-close>Cancelar</button>
                <button
                    type="submit"
                    class="btn btn-primary px-8 disabled:opacity-50 disabled:cursor-not-allowed"
                    data-ticket-submit
                >
                    Confirmar y pagar
                </button>
            </div>
        </div>
    </div>
</form>

@once
    @push('scripts')
        <script>
            (function () {
                function formatMXN(value) {
                    return new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
                }

                document.querySelectorAll('[data-ticket-checkout]').forEach(function (form) {
                    const inputs     = Array.from(form.querySelectorAll('[data-ticket-price]'));
                    const summaryEls = Array.from(form.querySelectorAll('[data-ticket-summary]'));
                    const totalEls   = Array.from(form.querySelectorAll('[data-ticket-total]'));
                    const modal      = form.querySelector('[data-ticket-modal]');
                    const openBtn    = form.querySelector('[data-ticket-open]');
                    const submitBtn  = form.querySelector('[data-ticket-submit]');
                    const openOnLoad = form.dataset.ticketOpenOnLoad === '1';
                    let addToCartSent = false;
                    let initiateCheckoutSent = false;
                    let hasItems = false;

                    function buildCommercePayload() {
                        const selectedInputs = inputs.filter(function (el) { return Number(el.value || 0) > 0; });
                        const value = selectedInputs.reduce(function (sum, el) {
                            return sum + (Number(el.value || 0) * Number(el.dataset.ticketPrice || 0));
                        }, 0);

                        return {
                            content_type: 'product',
                            content_ids: selectedInputs.map(function (el) { return el.dataset.ticketId; }),
                            contents: selectedInputs.map(function (el) {
                                return {
                                    id: el.dataset.ticketId,
                                    quantity: Number(el.value || 0),
                                    item_price: Number(el.dataset.ticketPrice || 0),
                                };
                            }),
                            content_name: selectedInputs.map(function (el) { return el.dataset.ticketName; }).join(', '),
                            currency: '{{ $currency }}',
                            value: Number(value.toFixed(2)),
                        };
                    }

                    function trackSiteEvent(name, options) {
                        if (window.LapsiqueTracker && typeof window.LapsiqueTracker.track === 'function') {
                            window.LapsiqueTracker.track(name, options || {});
                        }
                    }

                    function updateSummary() {
                        let total = 0;
                        const lines = [];

                        inputs.forEach(function (input) {
                            const qty   = Number(input.value || 0);
                            const price = Number(input.dataset.ticketPrice || 0);
                            if (qty > 0) {
                                const lineTotal = qty * price;
                                total += lineTotal;
                                lines.push(qty + ' × ' + input.dataset.ticketName + '  —  $' + formatMXN(lineTotal) + ' MXN');
                            }
                        });

                        hasItems = lines.length > 0;

                        summaryEls.forEach(function (el) {
                            if (!el) return;
                            el.textContent = hasItems ? lines.join('\n') : 'Selecciona una opción para ver el total.';
                            el.style.whiteSpace = 'pre-line';
                        });

                        totalEls.forEach(function (el) {
                            if (el) el.textContent = '$' + formatMXN(total) + ' MXN';
                        });

                        if (openBtn)   openBtn.disabled   = !hasItems;
                        if (submitBtn) submitBtn.disabled = !hasItems;
                    }

                    var bodyScrollTop = 0;
                    function openModal() {
                        if (!modal || !hasItems) return;
                        bodyScrollTop = window.scrollY || document.documentElement.scrollTop;
                        document.body.classList.add('guestlist-modal-open');
                        document.body.style.top = '-' + bodyScrollTop + 'px';
                        modal.classList.remove('hidden');
                        const firstInput = modal.querySelector('input[name="buyer_name"]');
                        if (firstInput) firstInput.focus();

                        const payload = buildCommercePayload();
                        if (!initiateCheckoutSent && payload.content_ids.length > 0) {
                            window.trackMetaPixel('InitiateCheckout', payload);
                            trackSiteEvent('checkout_started', {
                                category: 'commerce',
                                label: payload.content_name || 'ticket_checkout',
                                value: payload.value,
                                metadata: {
                                    content_ids: payload.content_ids,
                                },
                            });
                            initiateCheckoutSent = true;
                        }
                    }

                    function closeModal() {
                        if (!modal) return;
                        modal.classList.add('hidden');
                        document.body.classList.remove('guestlist-modal-open');
                        document.body.style.top = '';
                        window.scrollTo(0, bodyScrollTop);
                    }

                    inputs.forEach(function (input) {
                        input.addEventListener('input', function () {
                            updateSummary();
                            const hasSelected = inputs.some(function (el) { return Number(el.value || 0) > 0; });
                            if (hasSelected && !addToCartSent && window.trackMetaPixel) {
                                const payload = buildCommercePayload();
                                window.trackMetaPixel('AddToCart', payload);
                                trackSiteEvent('tickets_added_to_cart', {
                                    category: 'commerce',
                                    label: payload.content_name || 'ticket_selection',
                                    value: payload.value,
                                    metadata: {
                                        content_ids: payload.content_ids,
                                    },
                                });
                                addToCartSent = true;
                            }
                        });
                    });

                    if (openBtn) openBtn.addEventListener('click', openModal);

                    if (modal) {
                        modal.querySelectorAll('[data-ticket-close]').forEach(function (btn) {
                            btn.addEventListener('click', closeModal);
                        });
                    }

                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeModal();
                    });

                    form.addEventListener('submit', function () {
                        if (window.trackMetaPixel) {
                            const payload = buildCommercePayload();
                            window.trackMetaPixel('AddPaymentInfo', payload);
                            trackSiteEvent('checkout_submitted', {
                                category: 'commerce',
                                label: payload.content_name || 'ticket_checkout',
                                value: payload.value,
                                metadata: {
                                    content_ids: payload.content_ids,
                                },
                            });
                        }
                    });

                    updateSummary();

                    if (openOnLoad) {
                        // pequeño delay para que el DOM esté listo
                        setTimeout(function () { openModal(); }, 150);
                    }
                });
            })();
        </script>
    @endpush
@endonce
