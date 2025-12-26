<x-filament-panels::page>
    @php
        $status = $lastScan['status'] ?? null;
        $statusLabel = match ($status) {
            'checked_in' => 'Check-in confirmado',
            'duplicate' => 'Reescaneado',
            'limit_reached' => 'Consumos agotados',
            'read' => 'Marcado como leído',
            'rejected' => 'Rechazado',
            default => 'Sin escanear',
        };
        $statusClass = match ($status) {
            'checked_in' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
            'duplicate' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
            'limit_reached' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-300',
            'read' => 'bg-info-50 text-info-700 dark:bg-info-500/10 dark:text-info-300',
            'rejected' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-300',
            default => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300',
        };
    @endphp

    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <x-filament::section>
            <x-slot name="heading">
                Lector QR en tiempo real
            </x-slot>

            <div class="rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div
                    id="qr-reader"
                    data-component-id="{{ $this->getId() }}"
                    wire:ignore
                    class="min-h-[280px] overflow-hidden rounded-xl bg-gray-50 dark:bg-white/5"
                ></div>
            </div>

            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                Activa la cámara y acerca el QR del invitado. El check-in se confirma automáticamente.
            </p>

            <div class="mt-4 flex flex-wrap gap-2">
                <x-filament::button type="button" color="primary" id="qr-start" data-qr-action="start">Iniciar cámara</x-filament::button>
                <x-filament::button type="button" color="gray" id="qr-switch" data-qr-action="switch">Cambiar cámara</x-filament::button>
                <x-filament::button type="button" color="gray" id="qr-stop" data-qr-action="stop">Detener cámara</x-filament::button>
            </div>
            <div id="qr-status" class="mt-4 rounded-xl border border-gray-200/70 px-3 py-2 text-xs font-semibold text-gray-500 dark:border-white/10 dark:text-gray-400">
                Listo para escanear. Presiona "Iniciar camara".
            </div>

            <div class="mt-6 rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Código manual</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    Si el QR falla, ingresa el código manual o el token completo.
                </p>
                <div class="mt-3 flex flex-col gap-2 md:flex-row">
                    <input
                        type="text"
                        wire:model.defer="manualPayload"
                        placeholder="Ej: A1B2C3"
                        class="w-full rounded-lg border border-gray-200/70 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
                    />
                    <x-filament::button type="button" color="success" wire:click="submitManualPayload">
                        Validar
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Último escaneo
            </x-slot>

            @if($lastScan)
                <div class="space-y-4">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>

                    <div class="rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Invitado</p>
                        <p class="mt-2 text-lg font-semibold text-gray-950 dark:text-white">{{ $lastScan['guest'] ?? 'Invitado' }}</p>
                        @if(!empty($lastScan['email']))
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $lastScan['email'] }}</p>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Evento</p>
                        <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $lastScan['event'] ?? 'Evento' }}</p>
                        @if(!empty($lastScan['checked_in_at']))
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $lastScan['checked_in_at'] }}</p>
                        @endif
                        @if(isset($lastScan['remaining_uses'], $lastScan['check_in_limit']))
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Usos restantes: {{ $lastScan['remaining_uses'] }} de {{ $lastScan['check_in_limit'] }}
                            </p>
                        @endif
                    </div>
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-gray-200/70 bg-white p-6 text-center text-sm text-gray-500 shadow-sm dark:border-white/10 dark:bg-gray-900 dark:text-gray-400">
                    Escanea un QR para ver el resultado aquí.
                </div>
            @endif
        </x-filament::section>
    </div>

    @if($scanOverlay)
        @php
            $overlayTone = $scanOverlay['status'] ?? 'checked_in';
            $overlayToneClasses = match ($overlayTone) {
                'duplicate' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
                'limit_reached' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-300',
                'pending' => 'bg-info-50 text-info-700 dark:bg-info-500/10 dark:text-info-300',
                default => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
            };
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/70 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-3xl border border-white/10 bg-white p-6 shadow-2xl dark:bg-gray-900">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Lectura confirmada</p>
                        <h3 class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">
                            {{ $scanOverlay['guest'] ?? 'Invitado' }}
                        </h3>
                    </div>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $overlayToneClasses }}">
                        {{ $scanOverlay['status_label'] ?? 'Check-in' }}
                    </span>
                </div>

                <div class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <p><span class="font-semibold text-gray-900 dark:text-white">Acceso:</span> {{ $scanOverlay['event'] ?? 'Evento' }}</p>
                    <p><span class="font-semibold text-gray-900 dark:text-white">Lista de:</span> {{ $scanOverlay['list_owner'] ?? 'Guest List' }}</p>
                    <p><span class="font-semibold text-gray-900 dark:text-white">Hora de entrada:</span> {{ $scanOverlay['checked_in_at'] ?? '—' }}</p>
                    @if(isset($scanOverlay['remaining_uses'], $scanOverlay['check_in_limit']))
                        <p><span class="font-semibold text-gray-900 dark:text-white">Usos restantes:</span> {{ $scanOverlay['remaining_uses'] }} de {{ $scanOverlay['check_in_limit'] }}</p>
                    @endif
                </div>

                <div class="mt-6 flex flex-wrap justify-end gap-2">
                    <x-filament::button type="button" color="success" wire:click="confirmScan" wire:loading.attr="disabled">
                        Sí
                    </x-filament::button>
                    <x-filament::button type="button" color="gray" wire:click="rejectScan" wire:loading.attr="disabled">
                        No
                    </x-filament::button>
                    <x-filament::button type="button" color="primary" wire:click="markScanAsRead" wire:loading.attr="disabled">
                        Marcar como leído
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
                const scannerState = {
                    start: null,
                    stop: null,
                    switch: null,
                    isPaused: false,
                };

                const setStatus = (message, tone = 'idle') => {
                    const statusEl = document.getElementById('qr-status');
                    if (!statusEl) {
                        return;
                    }
                    statusEl.textContent = message;
                    statusEl.className = `${baseStatusClass} ${statusClasses[tone] ?? statusClasses.idle}`;
                };

                function setupQrScanner() {
                    const container = document.getElementById('qr-reader');
                    if (!container || container.dataset.initialized === '1') {
                        return false;
                    }

                    if (typeof Html5Qrcode === 'undefined') {
                        setStatus('No se pudo cargar el lector QR. Verifica la libreria.', 'error');
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
                    let isScanning = false;
                    let cameras = [];
                    let activeCameraId = null;

                    const pickPreferredCamera = (availableCameras) => {
                        if (!Array.isArray(availableCameras) || !availableCameras.length) {
                            return null;
                        }
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

                    const ensureCameras = async () => {
                        if (cameras.length) {
                            return cameras;
                        }
                        return loadCameras();
                    };

                    const config = {
                        fps: 12,
                        qrbox: { width: 260, height: 260 },
                    };

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
                            setStatus('La camara requiere HTTPS o localhost.', 'error');
                            return;
                        }

                        if (!navigator.mediaDevices?.getUserMedia) {
                            setStatus('Tu navegador no soporta acceso a camara.', 'error');
                            return;
                        }

                        setStatus('Solicitando acceso a la camara...', 'info');

                        await ensureCameras();
                        let cameraId = forcedCameraId ?? activeCameraId;

                        if (!cameraId) {
                            cameraId = pickPreferredCamera(cameras);
                        }

                        try {
                            if (cameraId) {
                                await html5QrCode.start(cameraId, config, onScanSuccess, onScanFailure);
                                activeCameraId = cameraId;
                            } else {
                                await html5QrCode.start({ facingMode: 'environment' }, config, onScanSuccess, onScanFailure);
                            }
                            isScanning = true;
                            setStatus('Camara activa. Escanea el QR.', 'success');
                        } catch (error) {
                            let started = false;

                            if (!cameraId) {
                                try {
                                    await html5QrCode.start({ facingMode: 'user' }, config, onScanSuccess, onScanFailure);
                                    isScanning = true;
                                    started = true;
                                    setStatus('Camara activa. Escanea el QR.', 'success');
                                } catch (fallbackError) {
                                }
                            }

                            if (!started) {
                                isScanning = false;
                                setStatus('No se pudo activar la camara. Revisa permisos.', 'error');
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
                            setStatus('Camara detenida.', 'warning');
                        } catch (error) {
                            isScanning = false;
                            setStatus('No se pudo detener la camara.', 'error');
                        }
                    };

                    const switchCamera = async () => {
                        if (!window.isSecureContext) {
                            setStatus('La camara requiere HTTPS o localhost.', 'error');
                            return;
                        }

                        if (!navigator.mediaDevices?.getUserMedia) {
                            setStatus('Tu navegador no soporta acceso a camara.', 'error');
                            return;
                        }

                        await ensureCameras();

                        if (!cameras.length) {
                            setStatus('No se detectaron camaras. Inicia la camara primero.', 'warning');
                            return;
                        }

                        if (cameras.length < 2) {
                            setStatus('Solo hay una camara disponible.', 'warning');
                            return;
                        }

                        const currentIndex = activeCameraId
                            ? cameras.findIndex((camera) => camera.id === activeCameraId)
                            : -1;
                        const nextIndex = currentIndex >= 0 ? (currentIndex + 1) % cameras.length : 0;
                        const nextCameraId = cameras[nextIndex]?.id;

                        if (!nextCameraId) {
                            setStatus('No se pudo cambiar de camara.', 'error');
                            return;
                        }

                        try {
                            if (isScanning) {
                                await html5QrCode.stop();
                                isScanning = false;
                                html5QrCode.clear();
                            }

                            await startScan(nextCameraId);
                        } catch (error) {
                            setStatus('No se pudo cambiar de camara. Revisa permisos.', 'error');
                        }
                    };

                    scannerState.start = startScan;
                    scannerState.stop = stopScan;
                    scannerState.switch = switchCamera;

                    setStatus('Listo para escanear. Presiona "Iniciar camara".', 'idle');

                    document.addEventListener('livewire:navigating', () => {
                        stopScan();
                    }, { once: true });

                    return true;
                }

                const handleActionClick = (event) => {
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
                            scannerState.start();
                        } else {
                            setStatus('No se pudo iniciar la camara. Recarga la pagina.', 'error');
                        }
                    } else if (trigger.dataset.qrAction === 'switch') {
                        if (scannerState.switch) {
                            scannerState.switch();
                        } else {
                            setStatus('No se pudo cambiar de camara. Recarga la pagina.', 'error');
                        }
                    } else if (trigger.dataset.qrAction === 'stop') {
                        if (scannerState.stop) {
                            scannerState.stop();
                        } else {
                            setStatus('No se pudo detener la camara.', 'error');
                        }
                    }
                };

                const bootScanner = () => {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', setupQrScanner, { once: true });
                    } else {
                        setupQrScanner();
                    }
                };

                const pauseScanner = () => {
                    scannerState.isPaused = true;
                    setStatus('Lectura pausada. Confirma el acceso para continuar.', 'warning');
                };

                const resumeScanner = () => {
                    scannerState.isPaused = false;
                    setStatus('Listo para escanear. Presiona "Iniciar camara".', 'idle');
                };

                document.addEventListener('click', handleActionClick);
                bootScanner();
                document.addEventListener('livewire:init', setupQrScanner);
                document.addEventListener('livewire:load', setupQrScanner);
                document.addEventListener('livewire:navigated', setupQrScanner);
                window.addEventListener('qr-overlay:pause', pauseScanner);
                window.addEventListener('qr-overlay:resume', resumeScanner);
            })();
        </script>
    @endpush
</x-filament-panels::page>
