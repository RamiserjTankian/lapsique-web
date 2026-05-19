<x-filament-panels::page>
    @php
        $currency = config('pos.currency', 'MXN');
        $selectedProduct = $this->selectedProduct;
        $filteredProducts = $this->filteredProducts;
        $unitPrice = (float) ($selectedProduct?->price ?? 0);
        $quantity = max((int) $selectedQuantity, 1);
        $total = $unitPrice * $quantity;
    @endphp

    <div class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
        <x-filament::section>
            <x-slot name="heading">Cajero POS</x-slot>

            <div class="rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div
                    id="qr-reader"
                    data-component-id="{{ $this->getId() }}"
                    wire:ignore
                    class="min-h-[280px] overflow-hidden rounded-xl bg-gray-50 dark:bg-white/5"
                ></div>
            </div>

            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                Flujo recomendado: selecciona el producto, ajusta cantidad y luego escanea el QR del cliente para confirmar el descuento.
            </p>

            <div class="mt-4 flex flex-wrap gap-2">
                <x-filament::button type="button" color="primary" id="qr-start" data-qr-action="start">Iniciar cámara</x-filament::button>
                <x-filament::button type="button" color="gray" id="qr-switch" data-qr-action="switch">Cambiar cámara</x-filament::button>
                <x-filament::button type="button" color="gray" id="qr-stop" data-qr-action="stop">Detener cámara</x-filament::button>
            </div>

            <div id="qr-status" class="mt-4 rounded-xl border border-gray-200/70 px-3 py-2 text-xs font-semibold text-gray-500 dark:border-white/10 dark:text-gray-400">
                Listo para escanear. Presiona "Iniciar cámara".
            </div>

            <div class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Código manual</p>
                <div class="mt-3 flex flex-col gap-2 md:flex-row">
                    <input
                        type="text"
                        wire:model.defer="manualPayload"
                        placeholder="Ej: A1B2C3"
                        class="w-full rounded-lg border border-gray-200/70 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                    <x-filament::button type="button" color="success" wire:click="submitManualPayload">
                        Consultar saldo
                    </x-filament::button>
                </div>
            </div>

            @if($lastCharge)
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/10">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700 dark:text-emerald-300">Último cargo</p>
                    <div class="mt-3 grid gap-2 text-sm text-emerald-900 dark:text-emerald-100">
                        <p><strong>Cliente:</strong> {{ $lastCharge['customer'] }}</p>
                        <p><strong>Evento:</strong> {{ $lastCharge['event'] }}</p>
                        <p><strong>Consumo:</strong> {{ $lastCharge['quantity'] }} × {{ $lastCharge['item'] }}</p>
                        <p><strong>Total:</strong> {{ number_format((float) $lastCharge['total'], 2) }} {{ $lastCharge['currency'] }}</p>
                        <p><strong>Saldo restante:</strong> {{ number_format((float) $lastCharge['balance_after'], 2) }} {{ $lastCharge['currency'] }}</p>
                        <p><strong>Hora:</strong> {{ $lastCharge['created_at'] }}</p>
                    </div>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Productos AyB</x-slot>

            @if(count($products))
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="setProductTypeFilter('beverage')"
                            class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $productTypeFilter === 'beverage' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300' }}"
                        >
                            Bebidas
                        </button>
                        <button
                            type="button"
                            wire:click="setProductTypeFilter('food')"
                            class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $productTypeFilter === 'food' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300' }}"
                        >
                            Alimentos
                        </button>
                    </div>

                    <input
                        type="text"
                        wire:model.live.debounce.250ms="productSearch"
                        placeholder="Buscar producto..."
                        class="w-full rounded-xl border border-gray-200/70 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />

                    <div class="max-h-[22rem] space-y-2 overflow-y-auto pr-1">
                        @forelse($filteredProducts as $product)
                            <button
                                type="button"
                                wire:click="selectProduct({{ $product->id }})"
                                class="w-full rounded-xl border px-3 py-3 text-left transition {{ $selectedProductId === $product->id ? 'border-primary-500 bg-primary-50 dark:border-primary-400 dark:bg-primary-500/10' : 'border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900' }}"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $product->name }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $product->notes ?: ($product->type === 'food' ? 'Alimento' : 'Bebida') }}</p>
                                    </div>
                                    <span class="shrink-0 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        {{ number_format((float) $product->price, 2) }} {{ $product->currency ?? $currency }}
                                    </span>
                                </div>
                            </button>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-200/70 bg-white p-5 text-center text-sm text-gray-500 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-400">
                                No encontramos productos {{ $productTypeFilter === 'food' ? 'de alimentos' : 'de bebidas' }} con ese filtro.
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-gray-200/70 bg-white p-6 text-center text-sm text-gray-500 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-400">
                    No hay productos AyB activos. Cárgalos primero desde la sección <strong>POS &gt; AyB</strong>.
                </div>
            @endif

            <div class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Cantidad</p>
                <div class="mt-3 flex items-center gap-3">
                    <x-filament::button type="button" color="gray" wire:click="$set('selectedQuantity', {{ max($quantity - 1, 1) }})">-</x-filament::button>
                    <input
                        type="number"
                        min="1"
                        wire:model.live="selectedQuantity"
                        class="w-24 rounded-lg border border-gray-200/70 bg-white px-3 py-2 text-center text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                    <x-filament::button type="button" color="gray" wire:click="$set('selectedQuantity', {{ $quantity + 1 }})">+</x-filament::button>
                </div>

                <div class="mt-5 rounded-2xl bg-gray-50 p-4 dark:bg-white/5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Resumen actual</p>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <p class="text-lg font-semibold text-gray-950 dark:text-white">{{ $selectedProduct?->name ?? 'Producto AyB' }}</p>
                        @if($selectedProduct)
                            <span class="rounded-full bg-gray-200 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-700 dark:bg-white/10 dark:text-gray-300">
                                {{ $selectedProduct->type === 'food' ? 'Alimento' : 'Bebida' }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $quantity }} × {{ number_format($unitPrice, 2) }} {{ $selectedProduct?->currency ?? $currency }}</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($total, 2) }} {{ $currency }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Movimientos recientes</p>
                <div class="mt-4 space-y-3">
                    @forelse($this->recentCharges as $charge)
                        <div class="rounded-xl border border-gray-200/70 bg-gray-50 p-3 dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-950 dark:text-white">{{ $charge->customer?->name ?? 'Cliente' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $charge->event?->title ?? 'Evento' }}</p>
                                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $charge->quantity }} × {{ $charge->item_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $charge->item_type === 'food' ? 'Alimento' : 'Bebida' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-950 dark:text-white">{{ number_format((float) $charge->total, 2) }} {{ $charge->currency }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Saldo: {{ number_format((float) $charge->balance_after, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-200/70 bg-white p-6 text-center text-sm text-gray-500 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-400">
                            Aún no hay cargos registrados en POS.
                        </div>
                    @endforelse
                </div>
            </div>
        </x-filament::section>
    </div>

    @if($scanOverlay)
        @php
            $canCharge = (bool) ($scanOverlay['can_charge'] ?? false);
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/70 p-4 backdrop-blur-sm">
            <div class="w-full max-w-xl rounded-3xl border border-white/10 bg-white p-6 shadow-2xl dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Confirmar cargo POS</p>
                        <h3 class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $scanOverlay['customer'] ?? 'Cliente' }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $scanOverlay['event'] ?? 'Evento' }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $canCharge ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300' : 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-300' }}">
                        {{ $canCharge ? 'Saldo disponible' : 'Saldo insuficiente' }}
                    </span>
                </div>

                <div class="mt-5 grid gap-3 text-sm text-gray-600 dark:text-gray-300 md:grid-cols-2">
                    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-white/5">
                        <p><strong>Ticket:</strong> {{ $scanOverlay['ticket'] ?? 'Ticket' }}</p>
                        <p class="mt-2"><strong>Código:</strong> {{ $scanOverlay['ticket_code'] ?? 'N/D' }}</p>
                        <p class="mt-2"><strong>Cliente:</strong> {{ $scanOverlay['email'] ?? 'Sin email' }}</p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-white/5">
                        <p><strong>Consumo:</strong> {{ $scanOverlay['quantity'] ?? 1 }} × {{ $scanOverlay['product_name'] ?? 'Producto AyB' }}</p>
                        <p class="mt-2"><strong>Tipo:</strong> {{ ($scanOverlay['product_type'] ?? 'beverage') === 'food' ? 'Alimento' : 'Bebida' }}</p>
                        <p class="mt-2"><strong>Total:</strong> {{ number_format((float) ($scanOverlay['total'] ?? 0), 2) }} {{ $scanOverlay['currency'] ?? $currency }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-950">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Saldo actual</span>
                        <span class="text-lg font-semibold text-gray-950 dark:text-white">{{ number_format((float) ($scanOverlay['balance_before'] ?? 0), 2) }} {{ $scanOverlay['currency'] ?? $currency }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-3">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Cargo POS</span>
                        <span class="text-lg font-semibold text-gray-950 dark:text-white">- {{ number_format((float) ($scanOverlay['total'] ?? 0), 2) }} {{ $scanOverlay['currency'] ?? $currency }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-3 border-t border-gray-200/70 pt-3 dark:border-white/10">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Saldo restante</span>
                        <span class="text-2xl font-semibold {{ $canCharge ? 'text-success-600 dark:text-success-300' : 'text-danger-600 dark:text-danger-300' }}">
                            {{ number_format((float) ($scanOverlay['balance_after'] ?? 0), 2) }} {{ $scanOverlay['currency'] ?? $currency }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap justify-end gap-2">
                    <x-filament::button type="button" color="success" wire:click="confirmCharge" wire:loading.attr="disabled">
                        Sí, descontar
                    </x-filament::button>
                    <x-filament::button type="button" color="gray" wire:click="cancelCharge" wire:loading.attr="disabled">
                        No
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
        <script>
            (function () {
                const cooldownMs = 1500;
                const baseStatusClass = 'mt-4 rounded-xl border px-3 py-2 text-xs font-semibold';
                const statusClasses = {
                    idle: 'border-gray-200/70 text-gray-500 dark:border-white/10 dark:text-gray-400',
                    info: 'border-info-200 text-info-700 dark:border-info-500/20 dark:text-info-300',
                    success: 'border-success-200 text-success-700 dark:border-success-500/20 dark:text-success-300',
                    warning: 'border-warning-200 text-warning-700 dark:border-warning-500/20 dark:text-warning-300',
                    error: 'border-danger-200 text-danger-700 dark:border-danger-500/20 dark:text-danger-300',
                };
                const scannerState = { start: null, stop: null, switch: null, isPaused: false };

                const setStatus = (message, tone = 'idle') => {
                    const statusEl = document.getElementById('qr-status');
                    if (!statusEl) return;
                    statusEl.textContent = message;
                    statusEl.className = `${baseStatusClass} ${statusClasses[tone] ?? statusClasses.idle}`;
                };

                function setupQrScanner() {
                    const container = document.getElementById('qr-reader');
                    if (!container || container.dataset.initialized === '1') {
                        return false;
                    }

                    if (typeof Html5Qrcode === 'undefined') {
                        setStatus('No se pudo cargar el lector QR. Verifica la librería.', 'error');
                        return false;
                    }

                    const componentId = container.dataset.componentId;
                    const livewireComponent = window.Livewire?.find(componentId);
                    if (!livewireComponent) {
                        setStatus('No se pudo iniciar el lector QR. Espera un momento y recarga.', 'error');
                        return false;
                    }

                    container.dataset.initialized = '1';
                    const html5QrCode = new Html5Qrcode('qr-reader');
                    let lastResult = null;
                    let lastScanAt = 0;
                    let cameras = [];
                    let activeCameraId = null;
                    let isScanning = false;

                    const pickPreferredCamera = (availableCameras) => {
                        if (!Array.isArray(availableCameras) || !availableCameras.length) return null;
                        const preferred = availableCameras.find((camera) => /back|rear|environment/i.test(camera.label));
                        return (preferred ?? availableCameras[0]).id ?? null;
                    };

                    const loadCameras = async () => {
                        try {
                            const list = await Html5Qrcode.getCameras();
                            cameras = Array.isArray(list) ? list : [];
                        } catch (error) {
                            cameras = [];
                        }
                        return cameras;
                    };

                    const ensureCameras = async () => cameras.length ? cameras : loadCameras();

                    const config = { fps: 12, qrbox: { width: 260, height: 260 } };

                    const onScanSuccess = (decodedText) => {
                        if (scannerState.isPaused) {
                            return;
                        }

                        const now = Date.now();
                        if (decodedText === lastResult && (now - lastScanAt) < cooldownMs) {
                            return;
                        }

                        lastResult = decodedText;
                        lastScanAt = now;
                        livewireComponent.call('handleScan', decodedText);
                    };

                    const onScanFailure = () => {};

                    const startScan = async (forcedCameraId = null) => {
                        if (isScanning) {
                            return;
                        }

                        if (!window.isSecureContext) {
                            setStatus('La cámara requiere HTTPS o localhost.', 'error');
                            return;
                        }

                        if (!navigator.mediaDevices?.getUserMedia) {
                            setStatus('Tu navegador no soporta acceso a cámara.', 'error');
                            return;
                        }

                        setStatus('Solicitando acceso a la cámara...', 'info');

                        const availableCameras = await ensureCameras();
                        if (!availableCameras.length) {
                            setStatus('No detectamos cámaras disponibles.', 'warning');
                            return;
                        }
                        activeCameraId = forcedCameraId ?? activeCameraId ?? pickPreferredCamera(availableCameras);
                        if (!activeCameraId) {
                            setStatus('No se pudo seleccionar una cámara.', 'warning');
                            return;
                        }

                        try {
                            await html5QrCode.start(activeCameraId, config, onScanSuccess, onScanFailure);
                            isScanning = true;
                            setStatus('Escaneando QR de cliente...', 'success');
                        } catch (error) {
                            let started = false;

                            try {
                                await html5QrCode.start({ facingMode: 'environment' }, config, onScanSuccess, onScanFailure);
                                isScanning = true;
                                started = true;
                                setStatus('Escaneando QR de cliente...', 'success');
                            } catch (fallbackError) {
                            }

                            if (!started) {
                                isScanning = false;
                                setStatus('No se pudo iniciar la cámara. Revisa permisos.', 'error');
                            }
                        }
                    };

                    const stopScan = async () => {
                        if (!isScanning) {
                            return;
                        }

                        try {
                            await html5QrCode.stop();
                            isScanning = false;
                            html5QrCode.clear();
                            setStatus('Cámara detenida.', 'warning');
                        } catch (error) {
                            isScanning = false;
                            setStatus('No se pudo detener la cámara.', 'error');
                        }
                    };

                    const switchCamera = async () => {
                        if (!window.isSecureContext) {
                            setStatus('La cámara requiere HTTPS o localhost.', 'error');
                            return;
                        }

                        if (!navigator.mediaDevices?.getUserMedia) {
                            setStatus('Tu navegador no soporta acceso a cámara.', 'error');
                            return;
                        }

                        const availableCameras = await ensureCameras();
                        if (!availableCameras.length) {
                            setStatus('No se detectaron cámaras. Inicia la cámara primero.', 'warning');
                            return;
                        }

                        if (availableCameras.length < 2) {
                            setStatus('Solo hay una cámara disponible.', 'warning');
                            return;
                        }

                        const currentIndex = availableCameras.findIndex((camera) => camera.id === activeCameraId);
                        const nextCamera = availableCameras[(currentIndex + 1) % availableCameras.length];

                        if (!nextCamera?.id) {
                            setStatus('No se pudo cambiar de cámara.', 'error');
                            return;
                        }

                        try {
                            if (isScanning) {
                                await html5QrCode.stop();
                                isScanning = false;
                                html5QrCode.clear();
                            }

                            await startScan(nextCamera.id);
                        } catch (error) {
                            setStatus('No se pudo cambiar de cámara. Revisa permisos.', 'error');
                        }
                    };

                    scannerState.start = startScan;
                    scannerState.stop = stopScan;
                    scannerState.switch = switchCamera;
                    setStatus('Listo para escanear. Presiona "Iniciar cámara".');

                    document.addEventListener('livewire:navigating', () => {
                        stopScan();
                    }, { once: true });

                    return true;
                }

                const handleActionClick = async (event) => {
                    const trigger = event.target.closest('[data-qr-action]');
                    if (!trigger) {
                        return;
                    }

                    event.preventDefault();

                    if (!scannerState.start || !scannerState.stop) {
                        setupQrScanner();
                    }

                    if (trigger.dataset.qrAction === 'start') {
                        if (scannerState.start) {
                            await scannerState.start();
                        } else {
                            setStatus('No se pudo iniciar la cámara. Recarga la página.', 'error');
                        }
                    } else if (trigger.dataset.qrAction === 'switch') {
                        if (scannerState.switch) {
                            await scannerState.switch();
                        } else {
                            setStatus('No se pudo cambiar de cámara. Recarga la página.', 'error');
                        }
                    } else if (trigger.dataset.qrAction === 'stop') {
                        if (scannerState.stop) {
                            await scannerState.stop();
                        } else {
                            setStatus('No se pudo detener la cámara.', 'error');
                        }
                    }
                };

                const boot = () => {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', setupQrScanner, { once: true });
                    } else {
                        setupQrScanner();
                    }
                };

                const pauseScanner = () => {
                    scannerState.isPaused = true;
                    setStatus('Confirmando cargo POS...', 'warning');
                };

                const resumeScanner = () => {
                    scannerState.isPaused = false;
                    setStatus('Listo para escanear. Presiona "Iniciar cámara".', 'idle');
                };

                document.addEventListener('click', handleActionClick);
                boot();
                document.addEventListener('livewire:init', setupQrScanner);
                document.addEventListener('livewire:load', setupQrScanner);
                document.addEventListener('livewire:navigated', setupQrScanner);
                window.addEventListener('qr-overlay:pause', pauseScanner);
                window.addEventListener('qr-overlay:resume', resumeScanner);
            })();
        </script>
    @endpush
</x-filament-panels::page>
