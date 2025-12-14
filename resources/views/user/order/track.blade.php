<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan #{{ $order->id }} - PasarNgalam</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-green': '#00E073',
                        'brand-dark': '#0F172A',
                        'brand-card': '#1E293B',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0F172A; color: white; }
        .glass-panel { background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
        [x-cloak] { display: none !important; }
        /* Star Rating Animation */
        .star-rating svg { transition: all 0.2s; }
        .star-rating svg:hover { transform: scale(1.2); }
    </style>
</head>
<body x-data="trackingData()">

    <!-- MODAL RATING (Hanya Muncul Jika showRatingModal = true) -->
    @if($showRatingModal)
    <div x-data="{ mRating: 0, dRating: 0 }" class="fixed inset-0 z-100 flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm">
        <div class="glass-panel w-full max-w-md rounded-3xl p-6 relative animate-bounce-in shadow-[0_0_50px_rgba(0,224,115,0.2)] border border-brand-green/30">
            
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-brand-green rounded-full flex items-center justify-center mx-auto mb-3 text-3xl shadow-lg shadow-brand-green/50">
                    🎉
                </div>
                <h2 class="text-2xl font-bold text-white">Pesanan Selesai!</h2>
                <p class="text-gray-400 text-sm">Gimana makanannya? Kasih nilai dong!</p>
            </div>

            <form action="{{ route('order.review', $order->id) }}" method="POST">
                @csrf
                
                <!-- Rating Merchant -->
                <div class="mb-6 bg-black/20 p-4 rounded-2xl border border-white/5">
                    <p class="text-center text-sm font-bold mb-3 text-brand-green uppercase tracking-wider">Nilai Restoran</p>
                    <div class="flex justify-center gap-2 mb-3 star-rating">
                        <!-- Looping Bintang AlpineJS -->
                        <template x-for="i in 5">
                            <svg @click="mRating = i" class="w-8 h-8 cursor-pointer" :class="i <= mRating ? 'text-yellow-400 fill-current' : 'text-gray-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </template>
                    </div>
                    <input type="hidden" name="merchant_rating" :value="mRating" required>
                    <textarea name="merchant_comment" class="w-full bg-gray-800/50 rounded-xl p-3 text-xs text-white border border-gray-600 focus:border-brand-green outline-none" placeholder="Tulis ulasan makanan..."></textarea>
                </div>

                <!-- Rating Driver (Jika ada) -->
                @if($order->driver_id)
                <div class="mb-6 bg-black/20 p-4 rounded-2xl border border-white/5">
                    <p class="text-center text-sm font-bold mb-3 text-blue-400 uppercase tracking-wider">Nilai Driver</p>
                    <div class="flex justify-center gap-2 mb-3 star-rating">
                        <template x-for="i in 5">
                            <svg @click="dRating = i" class="w-8 h-8 cursor-pointer" :class="i <= dRating ? 'text-yellow-400 fill-current' : 'text-gray-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </template>
                    </div>
                    <input type="hidden" name="driver_rating" :value="dRating" required>
                    <textarea name="driver_comment" class="w-full bg-gray-800/50 rounded-xl p-3 text-xs text-white border border-gray-600 focus:border-brand-green outline-none" placeholder="Pengirimannya aman?"></textarea>
                </div>
                @else
                    <input type="hidden" name="driver_rating" value="5">
                @endif

                <button type="submit" class="w-full bg-brand-green hover:bg-green-400 text-black font-bold py-3.5 rounded-xl shadow-lg transition transform hover:scale-[1.02]" :disabled="mRating == 0">
                    Kirim Penilaian
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- NAVBAR SIMPLE -->
    <nav class="border-b border-gray-800 bg-[#0F172A] sticky top-0 z-50">
        <div class="max-w-3xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-gray-400 hover:text-white flex items-center gap-2 text-sm font-medium">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Kembali
            </a>
            <span class="font-bold text-lg">Lacak Pesanan</span>
            <div class="w-10"></div> 
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 py-6 pb-24">

        @if(session('success'))
        <div class="mb-6 bg-green-500/20 border border-green-500/50 rounded-xl p-4 text-green-300 text-center font-bold animate-pulse">
            ✅ {{ session('success') }}
        </div>
        @endif

        <!-- 1. MAP SECTION -->
        <div class="glass-panel p-1 rounded-2xl mb-6 relative overflow-hidden h-[300px] md:h-[400px]">
            <div id="trackingMap" class="w-full h-full rounded-xl z-0"></div>
            
            <!-- Overlay Status Driver -->
            <div class="absolute bottom-4 left-4 right-4 bg-gray-900/90 backdrop-blur-md p-4 rounded-xl border border-gray-700 z-[1000] flex items-center gap-4"
                 x-show="hasDriver">
                <img :src="driver.photo" class="w-12 h-12 rounded-full object-cover border-2 border-brand-green">
                <div class="flex-1">
                    <p class="text-xs text-brand-green font-bold uppercase mb-0.5">Driver Menuju Lokasi</p>
                    <h4 class="font-bold text-white text-sm" x-text="driver.name"></h4>
                    <p class="text-xs text-gray-400" x-text="driver.plate"></p>
                    <p class="text-xs text-yellow-400" x-show="driver.rating">★ <span x-text="driver.rating"></span></p>
                </div>
                <a :href="'https://wa.me/' + driver.phone" target="_blank" class="bg-green-600 hover:bg-green-500 p-2.5 rounded-full text-white">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                </a>
            </div>
        </div>

        <!-- 2. STATUS TIMELINE -->
        <div class="glass-panel p-6 rounded-2xl mb-6">
            <h3 class="font-bold text-white text-lg mb-6">Status Pesanan</h3>
            
            <div class="relative pl-8 border-l-2 border-gray-700 space-y-8">
                <!-- Status 1: Pending -->
                <div class="relative">
                    <div class="absolute -left-[41px] top-0 w-6 h-6 rounded-full flex items-center justify-center border-2 
                        {{ in_array($order->status, ['pending', 'cooking', 'ready', 'delivery', 'completed']) ? 'bg-brand-green border-brand-green' : 'bg-gray-800 border-gray-600' }}">
                        <span class="text-black text-xs font-bold">✓</span>
                    </div>
                    <h4 class="font-bold {{ in_array($order->status, ['pending', 'cooking', 'ready', 'delivery', 'completed']) ? 'text-brand-green' : 'text-gray-500' }}">Pesanan Diterima</h4>
                    <p class="text-xs text-gray-400">Merchant telah menerima pesanan Anda.</p>
                </div>

                <!-- Status 2: Cooking -->
                <div class="relative">
                    <div class="absolute -left-[41px] top-0 w-6 h-6 rounded-full flex items-center justify-center border-2 
                        {{ in_array($order->status, ['cooking', 'ready', 'delivery', 'completed']) ? 'bg-brand-green border-brand-green' : 'bg-gray-800 border-gray-600' }}">
                        <span class="text-black text-xs font-bold">🍳</span>
                    </div>
                    <h4 class="font-bold {{ in_array($order->status, ['cooking', 'ready', 'delivery', 'completed']) ? 'text-white' : 'text-gray-500' }}">Sedang Disiapkan</h4>
                    <p class="text-xs text-gray-400">Makanan sedang dimasak.</p>
                </div>

                <!-- Status 3: Delivery -->
                <div class="relative">
                    <div class="absolute -left-[41px] top-0 w-6 h-6 rounded-full flex items-center justify-center border-2 
                        {{ in_array($order->status, ['delivery', 'completed']) ? 'bg-brand-green border-brand-green' : 'bg-gray-800 border-gray-600' }}">
                        <span class="text-black text-xs font-bold">🛵</span>
                    </div>
                    <h4 class="font-bold {{ in_array($order->status, ['delivery', 'completed']) ? 'text-white' : 'text-gray-500' }}">Sedang Diantar</h4>
                    <p class="text-xs text-gray-400">Driver menuju lokasi Anda.</p>
                </div>

                <!-- Status 4: Completed -->
                <div class="relative">
                    <div class="absolute -left-[41px] top-0 w-6 h-6 rounded-full flex items-center justify-center border-2 
                        {{ $order->status == 'completed' ? 'bg-brand-green border-brand-green' : 'bg-gray-800 border-gray-600' }}">
                        <span class="text-black text-xs font-bold">🏁</span>
                    </div>
                    <h4 class="font-bold {{ $order->status == 'completed' ? 'text-white' : 'text-gray-500' }}">Pesanan Selesai</h4>
                    <p class="text-xs text-gray-400">Selamat menikmati!</p>
                </div>
            </div>
        </div>

        <!-- 3. ORDER DETAIL -->
        <div class="glass-panel p-6 rounded-2xl">
            <h3 class="font-bold text-white mb-4">Rincian Pesanan</h3>
            <div class="space-y-4">
                @foreach($order->items as $item)
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-white font-medium text-sm">{{ $item['qty'] }}x {{ $item['name'] }}</p>
                        @if(!empty($item['addons']))
                            <p class="text-xs text-gray-500">+ {{ implode(', ', array_column($item['addons'], 'name')) }}</p>
                        @endif
                        @if(!empty($item['note']))
                            <p class="text-xs text-yellow-500/80 italic">"{{ $item['note'] }}"</p>
                        @endif
                    </div>
                    <p class="text-gray-300 text-sm font-bold">Rp {{ number_format($item['total'], 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
            <div class="border-t border-gray-700 mt-4 pt-4 flex justify-between items-center">
                <span class="text-gray-400">Total Bayar (Tunai)</span>
                <span class="text-xl font-bold text-brand-green">Rp {{ number_format($order->total_price + $order->delivery_fee, 0, ',', '.') }}</span>
            </div>
        </div>

    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        function trackingData() {
            return {
                driver: {
                    name: '{{ $order->driver->name ?? "Mencari Driver..." }}',
                    plate: '{{ $order->driver->vehicle_plate ?? "" }}',
                    phone: '{{ $order->driver->phone ?? "" }}',
                    photo: '{{ ($order->driver && $order->driver->profile_picture) ? asset("storage/".$order->driver->profile_picture) : "https://ui-avatars.com/api/?name=".($order->driver->name ?? "D")."&background=00E073&color=000" }}',
                    rating: '{{ $order->driver ? $order->driver->average_rating : "" }}',
                    lat: {{ $order->driver->latitude ?? 0 }},
                    lng: {{ $order->driver->longitude ?? 0 }}
                },
                hasDriver: {{ $order->driver_id ? 'true' : 'false' }},
                currentStatus: '{{ $order->status }}',
                
                merchantLat: {{ $order->merchant->latitude }},
                merchantLng: {{ $order->merchant->longitude }},
                destLat: {{ $order->dest_latitude }},
                destLng: {{ $order->dest_longitude }},

                map: null,
                driverMarker: null,

                init() {
                    this.initMap();
                    this.initEchoHook();
                    
                    if (window.Echo) {}

                    // --- FITUR AUTO RELOAD STATUS ---
                    setInterval(() => {
                        this.checkOrderStatus();
                    }, 5000); // Cek setiap 5 detik
                },

                // Fungsi cek status ke API
                checkOrderStatus() {
                    fetch('/api/order/{{ $order->id }}/location')
                        .then(response => response.json())
                        .then(data => {
                            // Jika status berubah jadi completed, reload halaman agar modal muncul
                            if (this.currentStatus !== 'completed' && data.status === 'completed') {
                                window.location.reload();
                            }
                            
                            // Update posisi driver di peta jika bergerak
                            if(data.driver_latitude && data.driver_longitude) {
                                this.updateDriverMarker(data.driver_latitude, data.driver_longitude);
                            }
                        });
                },

                initMap() {
                    this.map = L.map('trackingMap').setView([this.merchantLat, this.merchantLng], 14);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap'
                    }).addTo(this.map);

                    L.marker([this.merchantLat, this.merchantLng]).addTo(this.map)
                        .bindPopup("<b>{{ $order->merchant->store_name }}</b><br>Lokasi Ambil").openPopup();

                    L.marker([this.destLat, this.destLng]).addTo(this.map)
                        .bindPopup("<b>Lokasi Anda</b>");

                    if (this.hasDriver && this.driver.lat != 0) {
                        var driverIcon = L.divIcon({
                            className: 'bg-transparent',
                            html: '<div style="font-size: 24px;">🛵</div>'
                        });
                        
                        this.driverMarker = L.marker([this.driver.lat, this.driver.lng], {icon: driverIcon})
                            .addTo(this.map)
                            .bindPopup("Driver");
                    }

                    var bounds = L.latLngBounds([
                        [this.merchantLat, this.merchantLng],
                        [this.destLat, this.destLng]
                    ]);
                    this.map.fitBounds(bounds, { padding: [50, 50] });
                },

                updateDriverMarker(lat, lng) {
                    if (this.driverMarker) {
                        this.driverMarker.setLatLng([lat, lng]);
                    } else {
                        var driverIcon = L.divIcon({
                            className: 'bg-transparent',
                            html: '<div style="font-size: 24px;">🛵</div>'
                        });
                        this.driverMarker = L.marker([lat, lng], {icon: driverIcon}).addTo(this.map);
                        this.hasDriver = true;
                    }
                },
                initEchoHook() {
                    window.updateDriverMarkerHook = (lat, lng) => { this.updateDriverMarker(lat, lng); };
                }
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>
    <script>
        const EchoInstance = new Echo({
            broadcaster: 'reverb',
            key: '{{ env('REVERB_APP_KEY') }}',
            wsHost: '{{ env('REVERB_HOST', request()->getHost()) }}',
            wsPort: {{ env('REVERB_PORT', 443) }},
            wssPort: {{ env('REVERB_PORT', 443) }},
            forceTLS: true,
            enabledTransports: ['ws', 'wss'],
        });
        @if($order->driver_id)
        EchoInstance.channel('driver.{{ $order->driver_id }}')
            .listen('.driver.location.updated', (e) => {
                if (window.updateDriverMarkerHook) window.updateDriverMarkerHook(e.latitude, e.longitude);
            });
        @endif
        EchoInstance.channel('order.{{ $order->id }}')
            .listen('.order.updated', (e) => {
                if (e.status === 'completed') {
                    window.location.reload();
                }
            });
    </script>
</body>
</html>
