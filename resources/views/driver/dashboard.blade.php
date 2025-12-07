<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Panel - PasarNgalam</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
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
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    animation: {
                        'ping-slow': 'ping 2s cubic-bezier(0, 0, 0.2, 1) infinite',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-panel { background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-brand-dark text-white min-h-screen pb-20" 
      x-data="{ 
          showProfileModal: false,
          isOnline: @json((bool) $user->is_online), 

          toggleStatus() {
              this.isOnline = !this.isOnline;
              fetch('{{ route('driver.toggle') }}', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                  body: JSON.stringify({ status: this.isOnline })
              }).then(() => {
                  if(this.isOnline) updateDriverLocation(); // Langsung update lokasi jika ON
              });
          }
      }">

    <!-- NOTIFIKASI -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
         class="fixed top-24 right-4 z-50 bg-brand-green text-black px-6 py-3 rounded-xl font-bold shadow-lg transition-all">
        ✅ {{ session('success') }}
    </div>
    @endif

    <!-- NAVBAR -->
    <nav class="glass-panel sticky top-0 z-40 border-b-0 border-b-white/5">
        <div class="max-w-3xl mx-auto px-4 py-3 flex justify-between items-center">
            
            <!-- Kiri: Logo & Home -->
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="bg-gray-800 p-2 rounded-xl text-gray-400 hover:text-white hover:bg-gray-700 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div @click="showProfileModal = true" class="cursor-pointer">
                    <h1 class="text-sm font-bold text-white leading-tight">Halo, {{ explode(' ', $user->name)[0] }}</h1>
                    <p class="text-[10px] text-gray-400">{{ $user->vehicle_plate ?? 'Tanpa Plat' }}</p>
                </div>
            </div>
            
            <!--SWITCH ON/OFF -->
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold transition-colors" :class="isOnline ? 'text-brand-green' : 'text-gray-500'" x-text="isOnline ? 'SIAP ANTAR' : 'OFFLINE'"></span>
                <button @click="toggleStatus()" 
                    class="w-12 h-7 rounded-full p-1 transition-colors duration-300 focus:outline-none shadow-inner"
                    :class="isOnline ? 'bg-brand-green' : 'bg-gray-700'">
                    <div class="w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-300"
                         :class="isOnline ? 'translate-x-5' : 'translate-x-0'"></div>
                </button>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="max-w-3xl mx-auto px-4 mt-6">
        
        <!-- STATUS SALDO & ORDER -->
        <div class="grid grid-cols-2 gap-3 mb-8">
            <div class="glass-panel p-4 rounded-2xl">
                <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Dompet Tunai</p>
                <p class="text-xl font-bold text-brand-green">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</p>
            </div>
            <div class="glass-panel p-4 rounded-2xl">
                <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Orderan Hari Ini</p>
                <p class="text-xl font-bold text-white">{{ $todayOrders }} Selesai</p>
            </div>
        </div>

        @if($activeOrder)
            <!-- === TAMPILAN ORDER AKTIF === -->
            <div class="glass-panel border border-brand-green/50 rounded-4xl p-6 shadow-[0_0_50px_rgba(0,224,115,0.15)] relative overflow-hidden">
                <div class="text-center mb-6 relative z-10">
                    <div class="w-20 h-20 bg-brand-green rounded-full flex items-center justify-center mx-auto mb-4 text-black text-4xl shadow-lg shadow-brand-green/40 animate-bounce">🔔</div>
                    <h2 class="text-2xl font-bold text-white">Orderan Masuk!</h2>
                    <p class="text-gray-300 text-sm">Customer menunggumu.</p>
                </div>

                <div class="space-y-4 bg-[#0B1120]/50 p-5 rounded-2xl border border-white/5 relative z-10">
                    <div class="flex gap-4 border-b border-gray-700 pb-4">
                        <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center text-xl">🏪</div>
                        <div>
                            <p class="text-xs text-gray-400">Ambil di Warung</p>
                            <h4 class="font-bold text-white">{{ $activeOrder->merchant->store_name ?? 'Merchant' }}</h4>
                            <p class="text-xs text-gray-500">Total Barang: Rp {{ number_format($activeOrder->total_price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center text-xl">📍</div>
                        <div>
                            <p class="text-xs text-gray-400">Antar ke Pelanggan</p>
                            <h4 class="font-bold text-white">{{ $activeOrder->delivery_address }}</h4>
                            <!-- Format ongkir -->
                            <p class="text-xs text-brand-green font-bold">Ongkir Tunai: Rp {{ number_format($activeOrder->delivery_fee, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 space-y-3 relative z-10">
                    <!-- Link Google Maps Dinamis -->
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $activeOrder->dest_latitude }},{{ $activeOrder->dest_longitude }}" target="_blank" class="w-full bg-gray-800 hover:bg-gray-700 text-white text-center py-3.5 rounded-xl border border-gray-600 transition font-bold flex items-center justify-center gap-2">
                        <span>🗺️</span> Buka Google Maps
                    </a>
                    
                    <form action="{{ route('driver.order.complete', $activeOrder->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-brand-green hover:bg-green-400 text-black font-bold py-4 rounded-xl shadow-lg transition transform hover:scale-[1.02]">
                            Selesaikan Pengantaran
                        </button>
                    </form>
                </div>
            </div>

        @else
            <!-- === TAMPILAN STANDBY / OFFLINE === -->
            <div x-show="isOnline" class="text-center py-20 relative">
                <div class="relative w-40 h-40 mx-auto mb-8 flex items-center justify-center">
                    <div class="absolute inset-0 bg-brand-green/20 rounded-full animate-ping-slow"></div>
                    <div class="absolute inset-8 bg-brand-green/30 rounded-full animate-ping-slow" style="animation-delay: 0.5s"></div>
                    <div class="absolute inset-0 border border-brand-green/30 rounded-full"></div>
                    <div class="relative z-10 bg-[#0B1120] p-4 rounded-full border-2 border-brand-green shadow-[0_0_30px_rgba(0,224,115,0.4)]">
                        <span class="text-5xl">📡</span>
                    </div>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Mencari Orderan...</h2>
                <p class="text-gray-400 text-sm max-w-xs mx-auto">Tetap standby. Lokasimu sedang dipantau sistem untuk order terdekat.</p>
            </div>

            <div x-show="!isOnline" class="text-center py-20">
                <div class="w-32 h-32 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6 opacity-50 grayscale">
                    <span class="text-5xl">😴</span>
                </div>
                <h2 class="text-2xl font-bold text-gray-400 mb-2">Kamu Sedang Offline</h2>
                <p class="text-gray-500 text-sm max-w-xs mx-auto">Aktifkan tombol di atas untuk mulai bekerja.</p>
            </div>
        @endif

    </div>

    <!-- MODAL EDIT PROFIL -->
    <div x-show="showProfileModal" class="fixed inset-0 z-60 overflow-y-auto" x-cloak>
        <div class="fixed inset-0 bg-black/90 backdrop-blur-md" @click="showProfileModal = false"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="glass-panel w-full max-w-2xl p-8 rounded-3xl shadow-2xl relative">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-white">Profil Driver</h3>
                    <button @click="showProfileModal = false" class="text-gray-400 hover:text-white bg-gray-800 p-2 rounded-full">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf @method('PUT')

                    <!-- Profile Picture Upload -->
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase mb-3">Foto Profil</label>
                        <div class="relative w-32 h-32 mx-auto rounded-2xl overflow-hidden group cursor-pointer border-2 border-dashed border-gray-600 hover:border-brand-green transition bg-[#0B1120]">
                            <input type="file" name="profile_picture" class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewDriverProfile(this)">
                            
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 group-hover:text-brand-green transition z-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>

                            <img id="driver-profile-pic-preview" 
                                 src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=00E073&color=000&size=200' }}" 
                                 class="absolute inset-0 w-full h-full object-cover opacity-70 group-hover:opacity-40 transition">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-gray-400 text-xs font-bold uppercase mb-1">Nama</label><input type="text" name="name" value="{{ $user->name }}" class="w-full bg-[#0B1120] border border-gray-600 rounded-xl p-3 text-white focus:outline-none focus:border-brand-green"></div>
                        <div><label class="block text-gray-400 text-xs font-bold uppercase mb-1">No. HP</label><input type="text" name="phone" value="{{ $user->phone }}" class="w-full bg-[#0B1120] border border-gray-600 rounded-xl p-3 text-white focus:outline-none"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-brand-green text-xs font-bold uppercase mb-1">Plat Nomor</label><input type="text" name="vehicle_plate" value="{{ $user->vehicle_plate }}" class="w-full bg-[#0B1120] border border-brand-green/50 rounded-xl p-3 text-white focus:outline-none"></div>
                        <div><label class="block text-gray-400 text-xs font-bold uppercase mb-1">Email</label><input type="email" name="email" value="{{ $user->email }}" class="w-full bg-[#0B1120] border border-gray-600 rounded-xl p-3 text-white focus:outline-none"></div>
                    </div>

                    <div><label class="block text-gray-400 text-xs font-bold uppercase mb-1">Alamat</label><textarea name="address" rows="2" class="w-full bg-[#0B1120] border border-gray-600 rounded-xl p-3 text-white focus:outline-none resize-none">{{ $user->address }}</textarea></div>
                    
                    <div><label class="block text-gray-400 text-xs font-bold uppercase mb-1">Password Baru (Opsional)</label><input type="password" name="password" placeholder="Isi untuk ganti" class="w-full bg-[#0B1120] border border-gray-600 rounded-xl p-3 text-white focus:outline-none"></div>

                    <button type="submit" class="w-full bg-brand-green hover:bg-green-400 text-black font-bold py-3.5 rounded-xl shadow-lg mt-2 transition transform hover:scale-[1.02]">Simpan Profil</button>
                </form>

                <form action="{{ route('logout') }}" method="POST" class="mt-4 border-t border-gray-700 pt-4">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 text-red-400 hover:text-white py-2 rounded-xl hover:bg-red-500/10 transition">Keluar Aplikasi</button>
                </form>
            </div>
        </div>
    </div>

    <!-- PROFILE PICTURE PREVIEW SCRIPT -->
    <script>
        function previewDriverProfile(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('driver-profile-pic-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

    <!-- GPS TRACKER SCRIPT -->
    <script>
        function updateDriverLocation() {
            const isOnline = document.querySelector('[x-data]').__x.$data.isOnline;
            
            if (!isOnline) {
                console.log("Driver Offline - GPS Paused");
                return;
            }

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    fetch('/driver/update-location', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        })
                    }).then(() => console.log("GPS Sent: " + position.coords.latitude));
                });
            }
        }
        setInterval(updateDriverLocation, 10000);
    </script>

</body>
</html>