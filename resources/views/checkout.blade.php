<!-- File: resources/views/checkout.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - PasarNgalam</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-green': '#00E073'
                        , 'brand-dark': '#0F172A'
                        , 'brand-card': '#1E293B'
                    , }
                    , fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }

    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .glass-panel {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .form-input {
            background-color: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(71, 85, 105, 0.8);
            color: white;
            padding: 0.75rem;
            border-radius: 0.75rem;
            width: 100%;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #00E073;
            box-shadow: 0 0 0 2px rgba(0, 224, 115, 0.2);
        }

        [x-cloak] {
            display: none !important;
        }

    </style>
</head>
<body class="bg-brand-dark text-white min-h-screen" x-data="{
          cart: [],
          paymentMethod: 'qris', 
          loading: false,

          // Load data dari LocalStorage
          init() {
              const savedCart = localStorage.getItem('pasarNgalamCart');
              if (savedCart) {
                  this.cart = JSON.parse(savedCart);
              } else {
                  window.location.href = '/';
              }
          },

          formatRupiah(number) {
              return new Intl.NumberFormat('id-ID').format(number);
          },

          get subtotal() {
              return this.cart.reduce((sum, item) => sum + item.total, 0);
          },
          
          get tax() {
              return this.subtotal * 0.11; // PPN 11%
          },
          
          get deliveryFee() {
              return 5000;
          },

          get grandTotal() {
              return this.subtotal + this.tax + this.deliveryFee;
          },

          submitOrder() {
              this.loading = true;
              
              // Set data ke input hidden sebelum submit
              document.getElementById('cart_json').value = JSON.stringify(this.cart);
              document.getElementById('total_amount').value = this.grandTotal;
              
              // Submit Form Secara Manual
              document.getElementById('checkoutForm').submit();
              
              // Hapus keranjang localstorage setelah submit (agar bersih)
              localStorage.removeItem('pasarNgalamCart');
          }
      }">

    <!-- NAVBAR -->
    <nav class="border-b border-gray-800 bg-[#0F172A] sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-4">
            <a href="{{ url('/') }}" class="text-gray-400 hover:text-white flex items-center gap-2 text-sm font-medium">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Kembali
            </a>
            <div class="h-6 w-px bg-gray-700"></div>
            <span class="font-bold text-lg">Checkout Pesanan</span>
        </div>
    </nav>

    <!-- CONTENT -->
    <div class="max-w-7xl mx-auto px-4 py-8">

        <!-- FORM UTAMA -->
        <form id="checkoutForm" action="{{ route('checkout.process') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf
            <input type="hidden" name="cart_data" id="cart_json">
            <input type="hidden" name="total_amount" id="total_amount">
            <input type="hidden" name="latitude" id="lat_input">
            <input type="hidden" name="longitude" id="lng_input">

            <!-- LEFT COLUMN: FORM & PAYMENT -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Alamat Pengiriman -->
                <div class="glass-panel p-6 rounded-2xl">
                    <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <span class="bg-brand-green text-black w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                        Alamat Pengiriman
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nama Penerima</label>
                            <input type="text" name="customer_name" class="form-input" placeholder="Contoh: Budi Santoso" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nomor WhatsApp</label>
                            <input type="tel" name="customer_phone" class="form-input" placeholder="0812..." required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Alamat Lengkap</label>
                        <textarea name="delivery_address" rows="2" class="form-input" placeholder="Nama jalan, nomor rumah, patokan..." required></textarea>
                    </div>
                    
                    <!-- PETA LOKASI PENGIRIMAN -->
                    <div class="mt-4">
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Lokasi Pengiriman (Geser Pin)</label>
                        <div id="map-register" style="height: 200px; width: 100%; border-radius: 0.75rem; z-index: 0; margin-top: 10px; border: 1px solid #475569;"></div>
                        <p class="text-[10px] text-gray-500 mt-1">*Pastikan lokasi akurat agar driver menemukan alamat dengan mudah.</p>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="glass-panel p-6 rounded-2xl">
                    <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <span class="bg-brand-green text-black w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                        Pilih Pembayaran
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <!-- QRIS -->
                        <label class="cursor-pointer relative">
                            <input type="radio" name="payment_method" value="qris" x-model="paymentMethod" class="peer sr-only">
                            <div class="p-4 rounded-xl border border-gray-600 bg-gray-800/50 hover:bg-gray-800 peer-checked:border-brand-green peer-checked:bg-brand-green/10 transition flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded flex items-center justify-center p-1">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a2/Logo_QRIS.svg/1200px-Logo_QRIS.svg.png" class="max-w-full max-h-full">
                                </div>
                                <div>
                                    <div class="font-bold text-white">QRIS</div>
                                    <div class="text-xs text-gray-400">Scan & Bayar Instan</div>
                                </div>
                                <div class="ml-auto w-5 h-5 rounded-full border-2 border-gray-500 peer-checked:border-brand-green peer-checked:bg-brand-green"></div>
                            </div>
                        </label>

                        <!-- GOPAY -->
                        <label class="cursor-pointer relative">
                            <input type="radio" name="payment_method" value="gopay" x-model="paymentMethod" class="peer sr-only">
                            <div class="p-4 rounded-xl border border-gray-600 bg-gray-800/50 hover:bg-gray-800 peer-checked:border-brand-green peer-checked:bg-brand-green/10 transition flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded flex items-center justify-center p-1">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/Gopay_logo.svg/2560px-Gopay_logo.svg.png" class="max-w-full max-h-full">
                                </div>
                                <div>
                                    <div class="font-bold text-white">GoPay</div>
                                    <div class="text-xs text-gray-400">Sambungkan Akun</div>
                                </div>
                                <div class="ml-auto w-5 h-5 rounded-full border-2 border-gray-500 peer-checked:border-brand-green peer-checked:bg-brand-green"></div>
                            </div>
                        </label>

                        <!-- TRANSFER BANK -->
                        <label class="cursor-pointer relative">
                            <input type="radio" name="payment_method" value="bank" x-model="paymentMethod" class="peer sr-only">
                            <div class="p-4 rounded-xl border border-gray-600 bg-gray-800/50 hover:bg-gray-800 peer-checked:border-brand-green peer-checked:bg-brand-green/10 transition flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded flex items-center justify-center p-1">
                                    <svg class="w-6 h-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </div>
                                <div>
                                    <div class="font-bold text-white">Transfer Bank</div>
                                    <div class="text-xs text-gray-400">BCA, Mandiri, BRI</div>
                                </div>
                                <div class="ml-auto w-5 h-5 rounded-full border-2 border-gray-500 peer-checked:border-brand-green peer-checked:bg-brand-green"></div>
                            </div>
                        </label>

                        <!-- COD -->
                        <label class="cursor-pointer relative">
                            <input type="radio" name="payment_method" value="cod" x-model="paymentMethod" class="peer sr-only">
                            <div class="p-4 rounded-xl border border-gray-600 bg-gray-800/50 hover:bg-gray-800 peer-checked:border-brand-green peer-checked:bg-brand-green/10 transition flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded flex items-center justify-center p-1">
                                    <svg class="w-6 h-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                </div>
                                <div>
                                    <div class="font-bold text-white">Tunai / COD</div>
                                    <div class="text-xs text-gray-400">Bayar ke kurir</div>
                                </div>
                                <div class="ml-auto w-5 h-5 rounded-full border-2 border-gray-500 peer-checked:border-brand-green peer-checked:bg-brand-green"></div>
                            </div>
                        </label>

                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: ORDER SUMMARY -->
            <div class="lg:col-span-1">
                <div class="glass-panel p-6 rounded-2xl sticky top-24">
                    <h3 class="text-xl font-bold text-white mb-4">Ringkasan Pesanan</h3>

                    <!-- Loop Item Cart (Read-Only) -->
                    <div class="space-y-4 mb-6 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                        <template x-for="item in cart" :key="item.id">
                            <div class="flex gap-3">
                                <div class="w-12 h-12 rounded bg-gray-800 overflow-hidden shrink-0">
                                    <img :src="item.img" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-white font-medium" x-text="item.qty + 'x ' + item.name"></span>
                                        <span class="text-gray-300" x-text="formatRupiah(item.total)"></span>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1" x-show="item.addons.length > 0">
                                        + <span x-text="item.addons.map(a => a.name).join(', ')"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <hr class="border-gray-700 mb-4">

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-gray-400">
                            <span>Subtotal</span>
                            <span x-text="'Rp ' + formatRupiah(subtotal)"></span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Ongkos Kirim</span>
                            <span>Rp 5.000</span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Pajak & Layanan (11%)</span>
                            <span x-text="'Rp ' + formatRupiah(tax)"></span>
                        </div>
                        <div class="flex justify-between text-white font-bold text-lg pt-2 border-t border-gray-700 mt-2">
                            <span>Total Bayar</span>
                            <span class="text-brand-green" x-text="'Rp ' + formatRupiah(grandTotal)"></span>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <button type="button" @click="submitOrder()" class="w-full bg-brand-green hover:bg-green-400 text-black font-bold py-4 rounded-xl shadow-lg mt-6 transition transform hover:-translate-y-1 flex justify-center items-center gap-2" :disabled="loading">

                        <span x-show="!loading">Bayar Sekarang</span>

                        <!-- Loading Spinner -->
                        <div x-show="loading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-black"></div>
                    </button>

                    <p class="text-xs text-gray-500 text-center mt-4 flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Pembayaran Aman & Terenkripsi
                    </p>
                </div>
            </div>

        </form> <!-- END FORM -->
    </div>
    <!-- SCRIPT LOGIKA PETA (UPDATE INI) -->
    <script>
        // 1. Inisialisasi Peta
        // Default: Alun-alun Malang (Hanya untuk tampilan awal)
        var map = L.map('map').setView([-7.9826, 112.6308], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // 2. Tambah Marker
        var marker = L.marker([-7.9826, 112.6308], {
            draggable: true
        }).addTo(map);

        // 3. Update Input Hidden saat marker digeser
        function updateInput(lat, lng) {
            document.getElementById('lat_input').value = lat;
            document.getElementById('lng_input').value = lng;
            console.log("Koordinat tersimpan: " + lat + ", " + lng); // Cek di Console
        }

        // Listener saat pin digeser manual
        marker.on('dragend', function(event) {
            var position = marker.getLatLng();
            updateInput(position.lat, position.lng);
        });

        // 4. Geolocation (Lokasi Otomatis)
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;

                    // Pindah peta & marker ke lokasi user
                    map.setView([lat, lng], 17);
                    marker.setLatLng([lat, lng]);

                    // PENTING: Simpan ke input hidden
                    updateInput(lat, lng);
                }
                , function(error) {
                    // Jika GPS mati, pakai default tapi tetap isi input
                    updateInput(-7.9826, 112.6308);
                }
            );
        } else {
            // Jika browser tidak support GPS
            updateInput(-7.9826, 112.6308);
        }

        // 5. Validasi Tambahan saat tombol Bayar ditekan (AlpineJS Function)
        // Pastikan kode ini sinkron dengan x-data di atas
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkoutData', () => ({
                // ... (logika lain) ...

                submitOrder() {
                    // Cek apakah koordinat sudah terisi
                    var lat = document.getElementById('lat_input').value;
                    var lng = document.getElementById('lng_input').value;

                    if (!lat || !lng) {
                        alert("Mohon tunggu sebentar sampai peta memuat lokasi Anda, atau geser pin peta sedikit.");
                        return;
                    }

                    this.loading = true;
                    document.getElementById('cart_json').value = JSON.stringify(this.cart);
                    document.getElementById('total_amount').value = this.grandTotal;
                    document.getElementById('checkoutForm').submit();
                    localStorage.removeItem('pasarNgalamCart');
                }
            }))
        });

    </script>
</body>
</html>
