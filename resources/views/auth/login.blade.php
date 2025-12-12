<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk / Daftar - PasarNgalam</title>
    
    <!-- Tailwind & Alpine -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <!-- LEAFLET MAPS (PENTING) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'brand-green': '#00E073', 'brand-dark': '#0F172A', 'brand-card': '#1E293B' },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    animation: { 'float': 'float 6s ease-in-out infinite' },
                    keyframes: { float: { '0%, 100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-20px)' } } }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .form-input { background-color: rgba(30, 41, 59, 0.5); border: 1px solid rgba(71, 85, 105, 0.6); color: white; padding: 0.875rem 1rem; border-radius: 0.75rem; width: 100%; transition: all 0.2s; }
        .form-input:focus { outline: none; border-color: #00E073; box-shadow: 0 0 0 4px rgba(0, 224, 115, 0.1); background-color: rgba(30, 41, 59, 0.8); }
        [x-cloak] { display: none !important; }
        /* Map Style */
        #map-register { height: 200px; width: 100%; border-radius: 0.75rem; z-index: 0; margin-top: 10px; border: 1px solid #475569; touch-action: pan-x pan-y; }
    </style>
</head>
<body class="bg-brand-dark text-white min-h-screen flex items-center justify-center p-4 relative overflow-hidden" 
      x-data="{ tab: 'login', role: 'user' }">

    <!-- BACKGROUND BLOBS -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-brand-green/10 rounded-full blur-[100px] animate-float"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[100px] animate-float" style="animation-delay: 2s"></div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="relative z-10 w-full max-w-5xl bg-[#1E293B]/60 backdrop-blur-xl border border-white/10 rounded-2rem shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[600px]">
        
        <!-- LEFT SIDE: VISUAL -->
        <div class="hidden md:flex w-1/2 relative bg-gray-900 items-center justify-center p-12 overflow-hidden group">
            <div class="absolute inset-0 bg-brand-green/10 mix-blend-overlay z-10"></div>
            <img :src="role === 'merchant' ? 'https://images.unsplash.com/photo-1556910103-1c02745a30bf?q=80&w=800' : (role === 'driver' ? 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?q=80&w=800' : 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?q=80&w=1000')" 
                 class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:scale-105 transition duration-1000">
            <div class="absolute inset-0 bg-linear-to-t from-brand-dark via-brand-dark/40 to-transparent"></div>
            <div class="relative z-20 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-brand-green/20 backdrop-blur-md border border-brand-green/30 mb-6 shadow-[0_0_30px_rgba(0,224,115,0.3)]">
                    <svg class="w-8 h-8 text-brand-green" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-white mb-2">PasarNgalam</h2>
                <p class="text-gray-300 max-w-xs mx-auto" x-text="role === 'merchant' ? 'Kelola warungmu dan jangkau pelanggan lebih luas.' : (role === 'driver' ? 'Jadilah pahlawan pengantar makanan.' : 'Jelajahi kuliner legendaris Malang.')"></p>
            </div>
        </div>

        <!-- RIGHT SIDE: FORM -->
        <div class="w-full md:w-1/2 p-8 flex flex-col justify-center relative">
            <a href="{{ url('/') }}" class="absolute top-6 right-6 text-gray-400 hover:text-white transition"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></a>

            <!-- Tab Switcher -->
            <div class="flex p-1 bg-gray-800/50 rounded-xl mb-6 border border-white/5">
                <button @click="tab = 'login'" class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all" :class="tab === 'login' ? 'bg-brand-green text-black shadow-lg' : 'text-gray-400 hover:text-white'">Masuk</button>
                <button id="tab-register-btn" @click="tab = 'register'" class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all" :class="tab === 'register' ? 'bg-brand-green text-black shadow-lg' : 'text-gray-400 hover:text-white'">Daftar</button>
            </div>

            <!-- LOGIN FORM -->
            <div x-show="tab === 'login'" x-transition>
                <div class="mb-6">
                    <h3 class="text-2xl font-bold text-white">Selamat Datang! 👋</h3>
                    <p class="text-gray-400 text-sm mt-1">Masuk untuk melanjutkan.</p>
                </div>
                @if (session('error'))
                    <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/40 text-red-200 text-sm">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any() && session('login_errors'))
                    <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/40 text-red-200 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('login.process') }}" method="POST" class="space-y-4">
                    @csrf
                    <div><label class="block text-gray-400 text-xs font-bold uppercase mb-1">Email</label><input type="email" name="email" class="form-input" required></div>
                    <div><label class="block text-gray-400 text-xs font-bold uppercase mb-1">Password</label><input type="password" name="password" class="form-input" required></div>
                    <button type="submit" class="w-full bg-brand-green hover:bg-green-400 text-black font-bold py-3.5 rounded-xl shadow-[0_0_20px_rgba(0,224,115,0.3)] transition transform hover:-translate-y-0.5">Masuk Sekarang</button>
                </form>
            </div>

            <!-- REGISTER FORM -->
            <div x-show="tab === 'register'" x-transition style="display: none;">
                
                <!-- Role Selector -->
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-white mb-2">Daftar Sebagai:</h3>
                    <div class="flex gap-2 bg-gray-800/50 p-1.5 rounded-xl border border-white/5">
                        <button @click="role = 'user'" :class="role === 'user' ? 'bg-gray-700 text-brand-green border border-brand-green/30' : 'text-gray-400 hover:text-white'" class="flex-1 py-2 text-xs font-bold rounded-lg transition-all">Pembeli</button>
                        <button id="btn-role-merchant" @click="role = 'merchant'; setTimeout(() => { if(document.getElementById('map-register')) map.invalidateSize(); }, 100);" :class="role === 'merchant' ? 'bg-gray-700 text-brand-green border border-brand-green/30' : 'text-gray-400 hover:text-white'" class="flex-1 py-2 text-xs font-bold rounded-lg transition-all">Warung</button>
                        <button @click="role = 'driver'" :class="role === 'driver' ? 'bg-gray-700 text-brand-green border border-brand-green/30' : 'text-gray-400 hover:text-white'" class="flex-1 py-2 text-xs font-bold rounded-lg transition-all">Driver</button>
                    </div>
                </div>
                @if ($errors->any() && session('register_errors'))
                    <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/40 text-red-200 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register.process') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="role" :value="role">
                    
                    <!-- Input Tersembunyi Koordinat (Wajib untuk Merchant) -->
                    <input type="hidden" name="latitude" id="reg_lat">
                    <input type="hidden" name="longitude" id="reg_lng">

                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-gray-400 text-xs font-bold uppercase mb-1">Nama</label><input type="text" name="name" class="form-input" required></div>
                        <!-- WA Wajib -->
                        <div><label class="block text-gray-400 text-xs font-bold uppercase mb-1">WhatsApp</label><input type="tel" name="phone" placeholder="0812..." class="form-input" required></div>
                    </div>
                    
                    <div><label class="block text-gray-400 text-xs font-bold uppercase mb-1">Email</label><input type="email" name="email" class="form-input" required></div>

                    <!-- Input Khusus Merchant -->
                    <div x-show="role === 'merchant'" x-transition>
                        <label class="block text-brand-green text-xs font-bold uppercase mb-1">Nama Warung</label>
                        <input type="text" name="store_name" placeholder="Warung Makan..." class="form-input border-brand-green/50 bg-brand-green/5">
                        
                        <!-- PETA LOKASI WARUNG -->
                        <div class="mt-3">
                            <label class="block text-gray-300 text-xs mb-1">Lokasi Warung (Geser Pin)</label>
                            <div id="map-register"></div>
                            <p class="text-[10px] text-gray-500 mt-1">*Pastikan lokasi akurat agar driver tidak nyasar.</p>
                        </div>
                    </div>

                    <!-- Input Khusus Driver -->
                    <div x-show="role === 'driver'" x-transition class="grid grid-cols-2 gap-3">
                        <div><label class="block text-brand-green text-xs font-bold uppercase mb-1">Plat Nomor</label><input type="text" name="vehicle_plate" placeholder="N 1234 AB" class="form-input border-brand-green/50 bg-brand-green/5"></div>
                        <div><label class="block text-brand-green text-xs font-bold uppercase mb-1">Jenis</label>
                            <select name="vehicle_type" class="form-input border-brand-green/50 bg-brand-green/5 text-black">
                                <option value="motor">Motor</option><option value="mobil">Mobil</option>
                            </select>
                        </div>
                    </div>

                    <div><label class="block text-gray-400 text-xs font-bold uppercase mb-1">Password</label><input type="password" name="password" class="form-input" required></div>

                    <button type="submit" class="w-full bg-brand-green hover:bg-green-400 text-black font-bold py-3.5 rounded-xl shadow-[0_0_20px_rgba(0,224,115,0.3)] transition transform hover:-translate-y-0.5 mt-2">Daftar Sekarang</button>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPT PETA -->
    <script>
        let map;

        function initMap() {
            const container = document.getElementById('map-register');
            if (!container || map) return;

            map = L.map('map-register').setView([-7.9826, 112.6308], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            
            const marker = L.marker([-7.9826, 112.6308], {draggable: true}).addTo(map);

            function updateCoords(lat, lng) {
                document.getElementById('reg_lat').value = lat;
                document.getElementById('reg_lng').value = lng;
            }

            // Set awal
            updateCoords(-7.9826, 112.6308);

            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                updateCoords(pos.lat, pos.lng);
            });

            // Get Current Location (non-blocking)
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);
                    updateCoords(lat, lng);
                }, function(){}, { timeout: 5000 });
            }
        }

        function triggerMapResize() {
            if (map) {
                setTimeout(() => { map.invalidateSize(); }, 150);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Jika langsung mendarat di tab register, inisiasi map setelah render
            setTimeout(() => { initMap(); triggerMapResize(); }, 300);

            // Saat klik tab daftar
            const tabRegisterBtn = document.getElementById('tab-register-btn');
            if (tabRegisterBtn) {
                tabRegisterBtn.addEventListener('click', () => {
                    setTimeout(() => { initMap(); triggerMapResize(); }, 120);
                });
            }

            // Saat pilih role merchant (peta hanya muncul di merchant)
            const roleMerchantBtn = document.getElementById('btn-role-merchant');
            roleMerchantBtn?.addEventListener('click', () => {
                setTimeout(() => { initMap(); triggerMapResize(); }, 120);
            });
        });
    </script>

</body>
</html>