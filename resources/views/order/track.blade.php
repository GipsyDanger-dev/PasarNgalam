<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan #{{ $order->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- LEAFLET CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- LEAFLET ROUTING MACHINE CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { 'brand-green': '#00E073', 'brand-dark': '#0F172A', 'brand-card': '#1E293B' } }
            }
        }
    </script>
    <style>
        body { font-family: sans-serif; }
        .glass-panel { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); }
        #map { z-index: 1; }
        
        /* Hilangkan kotak putih petunjuk arah (Turn-by-turn instruction) agar map bersih */
        .leaflet-routing-container { display: none !important; }
    </style>
</head>
<body class="bg-brand-dark text-white min-h-screen pb-20">

    <!-- NAVBAR -->
    <nav class="border-b border-white/5 bg-[#0F172A] sticky top-0 z-50">
        <div class="max-w-3xl mx-auto px-4 h-16 flex items-center gap-4">
            <a href="{{ url('/') }}" class="bg-gray-800 p-2 rounded-xl text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="font-bold text-lg">Lacak Pesanan</h1>
                <p class="text-xs text-gray-400">ID: #ORD-{{ $order->id }}</p>
            </div>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 py-6 space-y-6">

        <!-- STATUS HEADER -->
        <div class="glass-panel p-6 rounded-3xl text-center relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-2xl font-bold text-brand-green mb-1 uppercase tracking-wider animate-pulse">
                    @if($order->status == 'pending') MENUNGGU KONFIRMASI ⏳
                    @elseif($order->status == 'cooking') SEDANG DIMASAK 🍳
                    @elseif($order->status == 'ready') MENUNGGU DRIVER 🛵
                    @elseif($order->status == 'delivery') SEDANG DIANTAR 🚀
                    @elseif($order->status == 'completed') PESANAN SELESAI 🎉
                    @endif
                </h2>
                <p class="text-gray-400 text-sm">
                    @if($order->status == 'completed') Selamat menikmati makananmu!
                    @else Estimasi sampai: 15-20 Menit
                    @endif
                </p>
            </div>
        </div>

        <!-- PETA UTAMA -->
        @if($order->status != 'completed')
        <div class="rounded-3xl overflow-hidden h-96 relative border-2 border-brand-green/30 shadow-[0_0_30px_rgba(0,224,115,0.1)]">
            <div id="map" class="w-full h-full bg-gray-800"></div>
            
            <!-- Loading jika driver belum ketemu -->
            @if(!$order->driver)
                <div class="absolute inset-0 bg-black/60 flex items-center justify-center z-[1000] backdrop-blur-sm pointer-events-none">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-green mx-auto mb-2"></div>
                        <p class="text-sm font-bold text-white">Mencari Driver Terdekat...</p>
                    </div>
                </div>
            @endif
        </div>
        @endif

        <!-- INFO DETAIL -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Driver Card -->
            <div class="glass-panel p-4 rounded-2xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center text-2xl overflow-hidden border border-gray-600">
                    @if($order->driver)
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($order->driver->name) }}&background=00E073&color=000" class="w-full h-full object-cover">
                    @else 🛵 @endif
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Driver</p>
                    <h3 class="font-bold text-white text-lg">{{ $order->driver->name ?? 'Sedang dicari...' }}</h3>
                    @if($order->driver)
                        <p class="text-xs text-brand-green font-mono">{{ $order->driver->vehicle_plate }}</p>
                    @endif
                </div>
            </div>

            <!-- Merchant Card -->
            <div class="glass-panel p-4 rounded-2xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center text-2xl overflow-hidden border border-gray-600">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($order->merchant->store_name) }}&background=random" class="w-full h-full object-cover">
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Warung</p>
                    <h3 class="font-bold text-white text-lg">{{ $order->merchant->store_name ?? 'Merchant' }}</h3>
                    <a href="https://wa.me/{{ $order->merchant->phone ?? '' }}" target="_blank" class="text-xs text-brand-green hover:underline flex items-center gap-1">
                        Hubungi Warung ↗
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- SCRIPT LEAFLET & ROUTING -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // 1. DATA KOORDINAT
            // Lokasi Customer (Tujuan)
            var destLat = {{ $order->dest_latitude ?? -7.9826 }};
            var destLng = {{ $order->dest_longitude ?? 112.6308 }};
            
            // Lokasi Merchant (Asal Makanan) - Fallback ke Alun-alun Malang jika merchant blm set lokasi
            var merchLat = {{ $order->merchant->latitude ?? -7.9826 }}; 
            var merchLng = {{ $order->merchant->longitude ?? 112.6308 }};

            // Lokasi Driver (Realtime)
            var driverLat = {{ $order->driver->latitude ?? 0 }};
            var driverLng = {{ $order->driver->longitude ?? 0 }};
            var hasDriver = {{ $order->driver ? 'true' : 'false' }};

            // 2. INIT MAP
            var map = L.map('map').setView([destLat, destLng], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            // 3. MARKER CUSTOMER (Tujuan)
            var markerCustomer = L.marker([destLat, destLng], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map);
            markerCustomer.bindPopup('<b>📍 Lokasi Tujuan</b><br>Pesanan Anda').openPopup();

            // 4. MARKER MERCHANT (Asal)
            var markerMerchant = L.marker([merchLat, merchLng], {
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }).addTo(map);
            markerMerchant.bindPopup('<b>🏪 Warung</b><br>{{ $order->merchant->store_name ?? "Merchant" }}');

            // 5. MARKER DRIVER (Jika ada)
            if (hasDriver && (driverLat !== 0 || driverLng !== 0)) {
                var markerDriver = L.marker([driverLat, driverLng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(map);
                markerDriver.bindPopup('<b>🛵 Driver</b><br>{{ $order->driver->name ?? "Driver" }}');
            }

            // 6. DRAW ROUTE (Merchant -> Driver -> Customer)
            if (hasDriver && (driverLat !== 0 || driverLng !== 0)) {
                // Route: Merchant -> Driver -> Customer
                L.Routing.control({
                    waypoints: [
                        L.latLng(merchLat, merchLng),  // Dari Merchant
                        L.latLng(driverLat, driverLng), // Ke Driver
                        L.latLng(destLat, destLng)      // Ke Customer
                    ],
                    routeWhileDragging: true,
                    show: false,
                    addWaypoints: false,
                    lineOptions: {
                        styles: [{color: '#00E073', opacity: 0.8, weight: 4}]
                    }
                }).addTo(map);
            } else {
                // Route: Merchant -> Customer (jika driver belum ditemukan)
                L.Routing.control({
                    waypoints: [
                        L.latLng(merchLat, merchLng),
                        L.latLng(destLat, destLng)
                    ],
                    routeWhileDragging: true,
                    show: false,
                    addWaypoints: false,
                    lineOptions: {
                        styles: [{color: '#00E073', opacity: 0.8, weight: 4}]
                    }
                }).addTo(map);
            }
        });
    </script>

</body>
</html>