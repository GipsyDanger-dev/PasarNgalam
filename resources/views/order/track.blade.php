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
    <!-- Pusher & Echo (for realtime updates). Make sure broadcasting is configured in .env -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.3/dist/echo.iife.js"></script>

    <script>
        let map;
        let markerDriver, markerMerchant, markerCustomer;
        let currentRoute;

        // Static data loaded from server (Blade -> JS). We'll update driver coords via API.
        const orderData = {
            destLat: {{ $order->dest_latitude ?? -7.9826 }},
            destLng: {{ $order->dest_longitude ?? 112.6308 }},
            merchLat: {{ $order->merchant->latitude ?? -7.9826 }},
            merchLng: {{ $order->merchant->longitude ?? 112.6308 }},
            driverLat: {{ $order->driver && $order->driver->latitude !== null ? $order->driver->latitude : 'null' }},
            driverLng: {{ $order->driver && $order->driver->longitude !== null ? $order->driver->longitude : 'null' }},
            hasDriver: {{ $order->driver ? 'true' : 'false' }} === 'true'
        };

        function initMap() {
            if (!map) {
                map = L.map('map').setView([orderData.destLat, orderData.destLng], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(map);
            }

            // Initial draw
            updateMarkersAndRoute();
        }

        function updateMarkersAndRoute() {
            const destLat = orderData.destLat;
            const destLng = orderData.destLng;
            const merchLat = orderData.merchLat;
            const merchLng = orderData.merchLng;
            const driverLat = orderData.driverLat;
            const driverLng = orderData.driverLng;
            const hasDriver = !!orderData.hasDriver && driverLat !== null && driverLng !== null;

            // CUSTOM ICON - Green untuk Customer (Tujuan)
            const greenIcon = L.icon({
                iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDQwIDQwIj48Y2lyY2xlIGN4PSIyMCIgY3k9IjIwIiByPSIxOCIgZmlsbD0iIzAwRTA3MyIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIi8+PHBhdHRlcm4gaWQ9ImRvdHMiIHg9IjAiIHk9IjAiIHdpZHRoPSI0IiBoZWlnaHQ9IjQiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiPjxyZWN0IHdpZHRoPSI0IiBoZWlnaHQ9IjQiIGZpbGw9IiMwMEVBNzMiLz48Y2lyY2xlIGN4PSIyIiBjeT0iMiIgcj0iMSIgZmlsbD0id2hpdGUiLz48L3BhdHRlcm4+PHRleHQgeD0iMjAiIHk9IjI0IiBmb250LXNpemU9IjE0IiBmb250LXdlaWdodD0iYm9sZCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0id2hpdGUiPvCfk5I8L3RleHQ+PC9zdmc+',
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });

            // CUSTOM ICON - Red untuk Merchant (Warung)
            const redIcon = L.icon({
                iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDQwIDQwIj48Y2lyY2xlIGN4PSIyMCIgY3k9IjIwIiByPSIxOCIgZmlsbD0iI0VGNDQ0NCIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIi8+PHRleHQgeD0iMjAiIHk9IjI0IiBmb250LXNpemU9IjE0IiBmb250LXdlaWdodD0iYm9sZCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0id2hpdGUiPvCfk5c8L3RleHQ+PC9zdmc+',
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });

            // CUSTOM ICON - Blue untuk Driver
            const blueIcon = L.icon({
                iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDQwIDQwIj48Y2lyY2xlIGN4PSIyMCIgY3k9IjIwIiByPSIxOCIgZmlsbD0iIzMzOTlGRiIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIi8+PHRleHQgeD0iMjAiIHk9IjI0IiBmb250LXNpemU9IjE0IiBmb250LXdlaWdodD0iYm9sZCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0id2hpdGUiPvu1s9G2IDwvdGV4dD48L3N2Zz4=',
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });

            // Customer marker (Green - Tujuan)
            if (!markerCustomer) {
                markerCustomer = L.marker([destLat, destLng], { icon: greenIcon }).addTo(map);
                markerCustomer.bindPopup('<b>📦 Lokasi Tujuan</b><br>Pesanan Anda');
            } else {
                markerCustomer.setLatLng([destLat, destLng]);
            }

            // Merchant marker (Red - Warung)
            if (!markerMerchant) {
                markerMerchant = L.marker([merchLat, merchLng], { icon: redIcon }).addTo(map);
                markerMerchant.bindPopup('<b>🍽️ Warung</b><br>{{ $order->merchant->store_name ?? "Merchant" }}');
            } else {
                markerMerchant.setLatLng([merchLat, merchLng]);
            }

            // Driver marker (Blue - Driver) (may be null)
            if (hasDriver) {
                if (!markerDriver) {
                    markerDriver = L.marker([driverLat, driverLng], { icon: blueIcon }).addTo(map);
                    markerDriver.bindPopup('<b>🛵 Driver</b><br>{{ $order->driver->name ?? "Driver" }}');
                } else {
                    markerDriver.setLatLng([driverLat, driverLng]);
                }
            } else if (markerDriver) {
                // remove old driver marker
                try { map.removeLayer(markerDriver); } catch(e) {}
                markerDriver = null;
            }

            // Remove old route
            if (currentRoute) {
                try { map.removeControl(currentRoute); } catch(e) {}
                currentRoute = null;
            }

            // Draw route
            if (hasDriver) {
                currentRoute = L.Routing.control({ waypoints: [ L.latLng(merchLat, merchLng), L.latLng(driverLat, driverLng), L.latLng(destLat, destLng) ], routeWhileDragging: false, show: false, addWaypoints: false, lineOptions: { styles: [{color: '#00E073', opacity: 0.8, weight: 5}] } }).addTo(map);
            } else {
                currentRoute = L.Routing.control({ waypoints: [ L.latLng(merchLat, merchLng), L.latLng(destLat, destLng) ], routeWhileDragging: false, show: false, addWaypoints: false, lineOptions: { styles: [{color: '#00E073', opacity: 0.8, weight: 5}] } }).addTo(map);
            }

            // Fit bounds
            const boundsArr = [ [destLat, destLng], [merchLat, merchLng] ];
            if (hasDriver) boundsArr.push([driverLat, driverLng]);
            try {
                const bounds = L.latLngBounds(boundsArr);
                map.fitBounds(bounds, { padding: [100, 100] });
            } catch (e) {
                // ignore if invalid bounds
            }
        }

        // 6. INIT MAP SAAT PAGE LOAD
        document.addEventListener("DOMContentLoaded", function() {
            initMap();
            
            // AUTO REFRESH SETIAP 5 DETIK UNTUK UPDATE DRIVER LOCATION (fallback polling)
            setInterval(function() {
                fetch('/api/order/{{ $order->id }}/location')
                    .then(response => response.json())
                    .then(data => {
                        // update orderData from API response (driver may be null)
                        orderData.driverLat = (data.driver_latitude !== null && data.driver_latitude !== 0) ? data.driver_latitude : null;
                        orderData.driverLng = (data.driver_longitude !== null && data.driver_longitude !== 0) ? data.driver_longitude : null;
                        orderData.hasDriver = orderData.driverLat !== null && orderData.driverLng !== null;

                        // update merchant/destination if backend provides fresher values
                        if (data.merchant_latitude !== undefined && data.merchant_longitude !== undefined) {
                            if (data.merchant_latitude !== null) orderData.merchLat = data.merchant_latitude;
                            if (data.merchant_longitude !== null) orderData.merchLng = data.merchant_longitude;
                        }
                        if (data.dest_latitude !== undefined && data.dest_longitude !== undefined) {
                            if (data.dest_latitude !== null) orderData.destLat = data.dest_latitude;
                            if (data.dest_longitude !== null) orderData.destLng = data.dest_longitude;
                        }

                        // Re-draw markers and route using updated orderData
                        updateMarkersAndRoute();
                    })
                    .catch(err => console.log('Location update error:', err));
            }, 5000);

            // Initialize Laravel Echo (Pusher) for realtime driver updates (best-effort)
            try {
                // Read config from env variables (may be empty in some setups)
                const pusherKey = '{{ env('PUSHER_APP_KEY', '') }}';
                const pusherCluster = '{{ env('PUSHER_APP_CLUSTER', '') }}';

                if (pusherKey) {
                    window.Pusher = Pusher;
                    window.Echo = new window.Echo({
                        broadcaster: 'pusher',
                        key: pusherKey,
                        cluster: pusherCluster || undefined,
                        forceTLS: {{ env('PUSHER_SCHEME', 'https') === 'https' ? 'true' : 'false' }},
                        encrypted: true,
                    });

                    // Subscribe to driver channel if we have a driver
                    if (orderData.hasDriver && orderData.driverLat !== null) {
                        const driverId = {{ $order->driver->id ?? 'null' }};
                        if (driverId) {
                            window.Echo.channel('driver.' + driverId)
                                .listen('.driver.location.updated', function(e) {
                                    // update orderData and redraw
                                    if (e && e.latitude && e.longitude) {
                                        orderData.driverLat = e.latitude;
                                        orderData.driverLng = e.longitude;
                                        orderData.hasDriver = true;
                                        updateMarkersAndRoute();
                                    }
                                });
                        }
                    }
                }
            } catch (e) {
                console.warn('Echo init error', e);
            }
        });
    </script>

</body>
</html>