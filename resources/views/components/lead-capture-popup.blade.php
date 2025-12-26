<div 
    x-data="leadCapturePopup()"
    x-show="showPopup"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click.self="closePopup"
    class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black bg-opacity-75"
    style="display: none;"
>
    <div 
        x-show="showPopup"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="transform scale-90 opacity-0"
        x-transition:enter-end="transform scale-100 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="transform scale-100 opacity-100"
        x-transition:leave-end="transform scale-90 opacity-0"
        class="relative w-full max-w-md p-8 bg-white rounded-lg shadow-2xl dark:bg-gray-800"
        @click.stop
    >
        {{-- Close Button --}}
        <button 
            @click="closePopup"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        {{-- Success Message --}}
        <div x-show="submitted" x-cloak class="text-center">
            <div class="mb-4 text-6xl">🎉</div>
            <h3 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white" x-text="successMessage"></h3>
            <p class="text-gray-600 dark:text-gray-300">¡Nos vemos en la pista!</p>
            <button 
                @click="closePopup"
                class="mt-6 px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition"
            >
                Cerrar
            </button>
        </div>

        {{-- Form --}}
        <div x-show="!submitted" x-cloak>
            <div class="mb-6 text-center">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    🎧 ¡Únete a Lapsique!
                </h2>
                <p class="text-gray-600 dark:text-gray-300">
                    Recibe noticias exclusivas sobre eventos, DJs y música electrónica.
                </p>
            </div>

            <form @submit.prevent="submitForm" class="space-y-4">
                {{-- Name --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nombre *
                    </label>
                    <input 
                        type="text"
                        x-model="formData.name"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="Tu nombre"
                    >
                </div>

                {{-- Email --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email *
                    </label>
                    <input 
                        type="email"
                        x-model="formData.email"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="tu@email.com"
                    >
                </div>

                {{-- Phone (Optional) --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        WhatsApp (opcional)
                    </label>
                    <input 
                        type="tel"
                        x-model="formData.phone"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="+52 123 456 7890"
                    >
                </div>

                {{-- Instagram (Optional) --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Instagram (opcional)
                    </label>
                    <input 
                        type="text"
                        x-model="formData.instagram_handle"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                        placeholder="@tuusuario"
                    >
                </div>

                {{-- Interests --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Intereses
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center">
                            <input type="checkbox" value="techno" x-model="formData.interests" class="mr-2">
                            <span class="text-sm dark:text-gray-300">Techno</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="house" x-model="formData.interests" class="mr-2">
                            <span class="text-sm dark:text-gray-300">House</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="minimal" x-model="formData.interests" class="mr-2">
                            <span class="text-sm dark:text-gray-300">Minimal</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" value="afterhours" x-model="formData.interests" class="mr-2">
                            <span class="text-sm dark:text-gray-300">After Hours</span>
                        </label>
                    </div>
                </div>

                {{-- Error Message --}}
                <div x-show="errorMessage" x-cloak class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
                    <span x-text="errorMessage"></span>
                </div>

                {{-- Submit Button --}}
                <button 
                    type="submit"
                    :disabled="loading"
                    class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!loading">¡Suscribirme! 🎉</span>
                    <span x-show="loading">Enviando...</span>
                </button>

                <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                    Al suscribirte aceptas recibir emails sobre eventos y noticias de Lapsique.
                </p>
            </form>
        </div>
    </div>
</div>

<script>
function leadCapturePopup() {
    return {
        showPopup: false,
        submitted: false,
        loading: false,
        errorMessage: '',
        successMessage: '',
        formData: {
            name: '',
            email: '',
            phone: '',
            instagram_handle: '',
            interests: []
        },

        init() {
            // Check if popup was already shown
            if (this.hasSeenPopup()) {
                return;
            }

            // Trigger popup based on user behavior
            this.setupTriggers();
        },

        setupTriggers() {
            // Exit intent
            document.addEventListener('mouseleave', (e) => {
                if (e.clientY < 0 && !this.showPopup) {
                    this.openPopup();
                }
            });

            // Scroll trigger (50%)
            let scrollTriggered = false;
            window.addEventListener('scroll', () => {
                if (scrollTriggered) return;
                
                const scrollPercentage = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
                if (scrollPercentage > 50) {
                    scrollTriggered = true;
                    setTimeout(() => this.openPopup(), 1000);
                }
            });

            // Time trigger (30 seconds)
            setTimeout(() => {
                if (!this.showPopup && !this.hasSeenPopup()) {
                    this.openPopup();
                }
            }, 30000);
        },

        hasSeenPopup() {
            const lastSeen = localStorage.getItem('lapsique_popup_seen');
            if (!lastSeen) return false;
            
            const daysSince = (Date.now() - parseInt(lastSeen)) / (1000 * 60 * 60 * 24);
            return daysSince < 7; // Don't show again for 7 days
        },

        openPopup() {
            if (this.hasSeenPopup()) return;
            this.showPopup = true;
        },

        closePopup() {
            this.showPopup = false;
            localStorage.setItem('lapsique_popup_seen', Date.now().toString());
        },

        async submitForm() {
            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route('leads.capture') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        ...this.formData,
                        current_page: window.location.href,
                        referrer: document.referrer,
                        utm_source: new URLSearchParams(window.location.search).get('utm_source'),
                        utm_medium: new URLSearchParams(window.location.search).get('utm_medium'),
                        utm_campaign: new URLSearchParams(window.location.search).get('utm_campaign'),
                        utm_term: new URLSearchParams(window.location.search).get('utm_term'),
                        utm_content: new URLSearchParams(window.location.search).get('utm_content'),
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.submitted = true;
                    this.successMessage = data.message;
                    localStorage.setItem('lapsique_popup_seen', Date.now().toString());
                    
                    // Reset form
                    this.formData = {
                        name: '',
                        email: '',
                        phone: '',
                        instagram_handle: '',
                        interests: []
                    };
                } else {
                    this.errorMessage = data.message || 'Ocurrió un error. Por favor intenta de nuevo.';
                }
            } catch (error) {
                console.error('Error submitting form:', error);
                this.errorMessage = 'Ocurrió un error. Por favor intenta de nuevo.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>

