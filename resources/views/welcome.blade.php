<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PasarNgalam - Kuliner Terbaik Malang</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <!-- Config Warna -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-green': '#00E073',
                        'brand-dark': '#0F172A',
                        'brand-card': '#1E293B',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        [x-cloak] { display: none !important; }
        .cart-badge { animation: bounce 0.5s; }
        @keyframes bounce { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }
    </style>
</head>
<body class="bg-brand-dark text-white min-h-screen relative selection:bg-brand-green selection:text-black" x-data="{
          showModal: false,
          modalView: 'merchant_detail', 
          
          selectedMerchant: { menus: [] },
          selectedMenu: {},
          cart: [],
          
          qty: 1,
          selectedAddons: [], 
          note: '',

          formatRupiah(number) {
              return new Intl.NumberFormat('id-ID').format(number);
          },

          get currentItemTotal() {
              if(!this.selectedMenu.price) return 0;
              let addonTotal = this.selectedAddons.reduce((sum, item) => sum + parseInt(item.price), 0);
              return (parseInt(this.selectedMenu.price) + addonTotal) * this.qty;
          },

          get grandTotal() {
              return this.cart.reduce((sum, item) => sum + item.total, 0);
          },

          get cartCount() {
              return this.cart.reduce((sum, item) => sum + item.qty, 0);
          },

          openMerchantModal(merchant) {
              this.selectedMerchant = merchant;
              this.modalView = 'merchant_detail';
              this.showModal = true;
          },

          openMenuCustomization(menu) {
              let dynamicAddons = [];
              if (menu.addons) {
                   if (Array.isArray(menu.addons)) {
                       dynamicAddons = menu.addons;
                   } else if (typeof menu.addons === 'string') {
                       try { dynamicAddons = JSON.parse(menu.addons); } catch (e) { dynamicAddons = []; }
                   }
              }

              this.selectedMenu = {
                  ...menu,
                  addons_available: dynamicAddons
              };
              
              this.qty = 1;
              this.selectedAddons = []; 
              this.note = '';
              this.modalView = 'menu_customization';
          },

          addToCart() {
              const item = {
                  id: Date.now(),
                  product_id: this.selectedMenu.id,
                  merchant_id: this.selectedMenu.merchant_id,
                  name: this.selectedMenu.name,
                  img: this.selectedMenu.img,
                  price: this.selectedMenu.price,
                  qty: this.qty,
                  addons: JSON.parse(JSON.stringify(this.selectedAddons)), 
                  note: this.note,
                  total: this.currentItemTotal,
                  // PENTING: Simpan koordinat merchant agar bisa dihitung jaraknya di checkout
                  merchant_lat: this.selectedMerchant.lat, 
                  merchant_lng: this.selectedMerchant.lng
              };
              
              this.cart.push(item);
              localStorage.setItem('pasarNgalamCart', JSON.stringify(this.cart));
              this.modalView = 'merchant_detail';
          },

          openCart() {
              this.modalView = 'cart_detail';
              this.showModal = true;
          },

          removeFromCart(id) {
              this.cart = this.cart.filter(item => item.id !== id);
              localStorage.setItem('pasarNgalamCart', JSON.stringify(this.cart));
          },

          processCheckout() {
              if (this.cart.length === 0) {
                  alert('Keranjang kosong!');
                  return;
              }
              window.location.href = '{{ route('checkout') }}';
          },

          backToMerchant() { this.modalView = 'merchant_detail'; },
          
          toggleAddon(addon) {
              const index = this.selectedAddons.findIndex(a => a.name === addon.name);
              if (index === -1) {
                  this.selectedAddons.push(addon); 
              } else {
                  this.selectedAddons.splice(index, 1); 
              }
          },
          
          init() {
              const savedCart = localStorage.getItem('pasarNgalamCart');
              if (savedCart) {
                  this.cart = JSON.parse(savedCart);
              }
          }
      }">

    <!-- NAVBAR -->
    <nav class="glass-panel fixed w-full z-50 top-0 transition-all border-b-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <div class="bg-brand-green p-2 rounded-xl shadow-[0_0_15px_rgba(0,224,115,0.4)]">
                            <svg class="h-6 w-6 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white tracking-tight hidden md:block">PasarNgalam</span>
                    </a>
                </div>

                <div class="hidden md:flex space-x-8 items-center">
                    <a href="#" class="text-white hover:text-brand-green font-medium transition">Beranda</a>
                    <a href="#" class="text-gray-300 hover:text-brand-green font-medium transition">Promo</a>
                    @auth
                        @if(Auth::user()->role == 'merchant')
                        <a href="{{ route('merchant.dashboard') }}" class="text-brand-green font-bold border border-brand-green/30 px-4 py-1.5 rounded-full hover:bg-brand-green hover:text-black transition">Dashboard Warung</a>
                        @elseif(Auth::user()->role == 'driver')
                        <a href="{{ route('driver.dashboard') }}" class="text-brand-green font-bold border border-brand-green/30 px-4 py-1.5 rounded-full hover:bg-brand-green hover:text-black transition">Panel Driver</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-brand-green font-bold border border-brand-green/30 px-4 py-1.5 rounded-full hover:bg-brand-green hover:text-black transition shadow-[0_0_10px_rgba(0,224,115,0.2)]">Gabung Mitra</a>
                    @endauth
                </div>

                <div class="flex items-center gap-4">
                    <button @click="openCart()" class="relative bg-gray-800 p-2.5 rounded-xl hover:bg-gray-700 transition border border-gray-600 group">
                        <svg class="w-6 h-6 text-gray-300 group-hover:text-brand-green transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <div x-show="cartCount > 0" x-transition.scale class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-[#0F172A] cart-badge" x-text="cartCount"></div>
                    </button>

                    @auth
                        @php
                        $activeOrder = \App\Models\Order::where('customer_id', Auth::id())
                        ->where('status', '!=', 'completed')
                        ->latest()->first();
                        @endphp

                        @if($activeOrder)
                        <a href="{{ route('order.track', $activeOrder->id) }}" class="bg-blue-600/20 text-blue-400 hover:text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 border border-blue-600/30 animate-pulse mr-2 hidden sm:flex">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Lacak
                        </a>
                        @endif

                        <div class="text-right hidden sm:block mr-2">
                            <p class="text-xs text-gray-400">Halo,</p>
                            <p class="text-sm font-bold text-white">{{ Auth::user()->name }}</p>
                        </div>
                        
                        <a href="{{ route('profile.show') }}" class="bg-gray-700 hover:bg-gray-600 text-white p-2.5 rounded-xl transition border border-gray-600" title="Profil">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-red-500/20 hover:bg-red-600 text-red-400 hover:text-white p-2.5 rounded-xl transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="bg-brand-card hover:bg-gray-700 text-white border border-gray-600 px-5 py-2.5 rounded-xl font-bold text-sm transition hidden sm:block">Masuk</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <div class="relative h-[500px] w-full flex items-center justify-center mt-20">
        <div class="absolute inset-0 overflow-hidden">
            <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?q=80&w=1920&fit=crop" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-brand-dark/80 to-brand-dark/40"></div>
        </div>
        <div class="relative z-10 text-center px-4 w-full max-w-4xl">
            <h1 class="text-5xl md:text-7xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-brand-green to-teal-400 mb-6 drop-shadow-2xl">Kuliner Ngalam</h1>
            <p class="text-xl text-gray-200 mb-10 font-light max-w-2xl mx-auto">Temukan cita rasa legendaris dan dukung UMKM lokal langsung dari smartphone Anda.</p>
            
            <div class="relative max-w-xl mx-auto">
                <input type="text" class="block w-full pl-8 pr-24 py-5 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-green shadow-2xl transition" placeholder="Cari Toko atau Warung...">
                <button class="absolute right-2 top-2 bottom-2 bg-brand-green hover:bg-green-400 text-black px-8 rounded-full font-bold transition">Cari</button>
            </div>
        </div>
    </div>

    <!-- CONTENT GRID -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-20 pb-20">
        @if($merchants->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($merchants as $merchant)
            @php
                // 1. LOGIKA MENU
                $menusData = $merchant->products->map(function($p) use ($merchant) {
                    $addons = [];
                    if (!empty($p->addons)) {
                        if (is_array($p->addons)) {
                            $addons = $p->addons;
                        } elseif (is_string($p->addons)) {
                            $decoded = json_decode($p->addons, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $addons = $decoded;
                            }
                        }
                    }

                    $imageUrl = !empty($p->image) 
                        ? asset('storage/' . $p->image) 
                        : 'https://placehold.co/400x300?text=No+Image';

                    return [
                        'id' => $p->id,
                        'merchant_id' => $merchant->id,
                        'name' => $p->name,
                        'price' => $p->price,
                        'desc' => $p->description,
                        'img' => $imageUrl, 
                        'addons' => $addons
                    ];
                })->values()->toArray();

                // 2. LOGIKA BANNER TOKO & KOORDINAT
                $bannerPath = $merchant->banner ?? $merchant->store_banner;
                if (!empty($bannerPath)) {
                    $merchantImg = asset('storage/' . $bannerPath);
                } else {
                    $merchantImg = 'https://ui-avatars.com/api/?name='.urlencode($merchant->store_name).'&background=00E073&color=000&size=400&bold=true&font-size=0.33';
                }

                // Susun Data Merchant Final (Termasuk LAT/LNG untuk hitung ongkir)
                $merchantData = [
                    'id' => $merchant->id,
                    'name' => $merchant->store_name ?? $merchant->name,
                    'lat' => $merchant->latitude,   // PENTING: Untuk Ongkir
                    'lng' => $merchant->longitude,  // PENTING: Untuk Ongkir
                    'category' => 'Aneka Kuliner',
                    'rating' => '4.8',
                    'img' => $merchantImg, 
                    'menus' => $menusData
                ];
            @endphp

            <!-- CARD MERCHANT -->
            <div @click="openMerchantModal({{ json_encode($merchantData) }})" class="glass-panel rounded-3xl overflow-hidden hover:border-brand-green/50 transition duration-300 group cursor-pointer relative shadow-lg hover:shadow-brand-green/20">
                <div class="relative h-48 overflow-hidden bg-gray-800">
                    <!-- FOTO BANNER -->
                    <img src="{{ $merchantData['img'] }}" 
                         class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                         onerror="this.onerror=null; this.src='https://placehold.co/600x400?text=No+Image';">
                    
                    <!-- Overlay Gelap -->
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/90 via-transparent to-transparent"></div>
                    
                    <!-- Badge Status Buka -->
                    <div class="absolute top-4 right-4 bg-brand-green text-black text-xs font-bold px-3 py-1.5 rounded-full shadow-lg flex items-center gap-1">
                        <span class="w-2 h-2 bg-black rounded-full animate-pulse"></span> Buka
                    </div>
                </div>
                
                <div class="p-6 relative">
                    <!-- Judul Toko -->
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-white truncate group-hover:text-brand-green transition pr-4">
                            {{ $merchantData['name'] }}
                        </h3>
                        <div class="flex items-center gap-1 bg-yellow-500/10 px-2 py-1 rounded-lg border border-yellow-500/20 shrink-0">
                            <span class="font-bold text-yellow-500 text-sm">★ {{ $merchantData['rating'] }}</span>
                        </div>
                    </div>
                    
                    <!-- Info Tambahan -->
                    <p class="text-gray-400 text-sm mb-4 truncate flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        {{ $merchantData['category'] }} • {{ count($merchantData['menus']) }} Menu
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Tampilan Jika Belum Ada Warung -->
        <div class="text-center py-20 glass-panel rounded-3xl border border-dashed border-gray-700">
            <div class="text-6xl mb-4 grayscale opacity-50">🏪</div>
            <h2 class="text-2xl font-bold text-white mb-2">Belum Ada Warung Buka</h2>
            <p class="text-gray-400">Jadilah mitra pertama kami dan mulai berjualan!</p>
            <a href="{{ route('login') }}" class="inline-block mt-6 text-brand-green font-bold hover:underline">Gabung Sebagai Mitra &rarr;</a>
        </div>
        @endif
    </div>

    <!-- MODAL POPUP -->
    <div x-show="showModal" class="fixed inset-0 z-[60] overflow-y-auto" x-cloak>
        <div x-show="showModal" x-transition.opacity @click="showModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>
        <div x-show="showModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-[#1E293B] border border-gray-700 w-full max-w-lg rounded-3xl shadow-2xl relative overflow-hidden flex flex-col max-h-[90vh]">

                <!-- HEADER MODAL -->
                <div class="p-6 border-b border-gray-700 flex justify-between items-center bg-[#0F172A]">
                    <h3 class="text-xl font-bold text-white">
                        <span x-show="modalView === 'merchant_detail'" x-text="selectedMerchant.name"></span>
                        <span x-show="modalView === 'menu_customization'">Pesan Menu</span>
                        <span x-show="modalView === 'cart_detail'">Keranjang Saya</span>
                    </h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-white bg-gray-800 p-2 rounded-full"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <!-- BODY -->
                <div class="overflow-y-auto p-6 flex-1 no-scrollbar">

                    <!-- VIEW 1: MERCHANT DETAIL -->
                    <div x-show="modalView === 'merchant_detail'">
                        <img :src="selectedMerchant.img" class="w-full h-48 object-cover rounded-2xl mb-6 border border-gray-600 bg-gray-800">
                        <h4 class="text-gray-400 text-sm font-bold uppercase tracking-wider mb-4">Daftar Menu</h4>
                        <div class="space-y-4">
                            <template x-for="menu in selectedMerchant.menus" :key="menu.id">
                                <div class="flex gap-4 p-4 rounded-xl border border-gray-700 bg-gray-800/50 hover:border-brand-green/50 cursor-pointer transition" @click="openMenuCustomization(menu)">
                                    <img :src="menu.img" class="w-20 h-20 rounded-lg object-cover bg-gray-700">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-white text-lg" x-text="menu.name"></h4>
                                        <p class="text-gray-400 text-xs line-clamp-2" x-text="menu.desc || 'Tidak ada deskripsi'"></p>
                                        <div class="mt-2 flex justify-between items-center">
                                            <span class="text-brand-green font-bold" x-text="'Rp ' + formatRupiah(menu.price)"></span>
                                            <button class="bg-gray-700 hover:bg-brand-green hover:text-black text-white px-3 py-1 rounded-lg text-xs font-bold transition">+ Tambah</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!selectedMerchant.menus || selectedMerchant.menus.length === 0">
                                <div class="text-center py-8 text-gray-500">Warung ini belum mengupload menu.</div>
                            </template>
                        </div>
                    </div>

                    <!-- VIEW 2: MENU CUSTOMIZATION -->
                    <div x-show="modalView === 'menu_customization'">
                        <button @click="backToMerchant()" class="text-gray-400 text-sm hover:text-white mb-4 flex items-center gap-1">&larr; Kembali ke Menu</button>
                        <div class="flex gap-4 mb-6">
                            <img :src="selectedMenu.img" class="w-24 h-24 rounded-xl object-cover border border-gray-600 bg-gray-800">
                            <div>
                                <h2 class="text-2xl font-bold text-white" x-text="selectedMenu.name"></h2>
                                <p class="text-brand-green font-bold text-lg" x-text="'Rp ' + formatRupiah(selectedMenu.price)"></p>
                            </div>
                        </div>

                        <!-- ADDONS SECTION -->
                        <template x-if="selectedMenu.addons_available && selectedMenu.addons_available.length > 0">
                            <div class="mb-6">
                                <h4 class="font-bold text-white mb-3">Tambahan (Opsional)</h4>
                                <div class="space-y-2">
                                    <template x-for="addon in selectedMenu.addons_available" :key="addon.name">
                                        <label class="flex items-center justify-between p-3 rounded-lg border border-gray-700 cursor-pointer hover:bg-gray-800 transition">
                                            <div class="flex items-center gap-3">
                                                <input type="checkbox" @change="toggleAddon(addon)" class="w-5 h-5 rounded border-gray-600 bg-gray-700 text-brand-green focus:ring-brand-green">
                                                <span class="text-gray-300 text-sm" x-text="addon.name"></span>
                                            </div>
                                            <span class="text-gray-400 text-xs" x-text="addon.price > 0 ? '+Rp ' + formatRupiah(addon.price) : 'Gratis'"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div class="mb-6">
                            <h4 class="font-bold text-white mb-2">Catatan Pesanan</h4>
                            <textarea x-model="note" class="w-full bg-gray-900 border border-gray-700 rounded-xl p-3 text-white focus:border-brand-green focus:ring-0 text-sm" placeholder="Contoh: Jangan pakai bawang goreng..."></textarea>
                        </div>

                        <div class="flex items-center justify-between bg-gray-900 p-4 rounded-xl border border-gray-700">
                            <span class="font-bold text-white">Jumlah</span>
                            <div class="flex items-center gap-4">
                                <button @click="qty > 1 ? qty-- : null" class="w-8 h-8 rounded-full bg-gray-700 text-white flex items-center justify-center hover:bg-gray-600">-</button>
                                <span class="font-bold text-xl text-white w-8 text-center" x-text="qty"></span>
                                <button @click="qty++" class="w-8 h-8 rounded-full bg-brand-green text-black flex items-center justify-center hover:bg-green-500">+</button>
                            </div>
                        </div>
                    </div>

                    <!-- VIEW 3: CART DETAIL -->
                    <div x-show="modalView === 'cart_detail'">
                        <template x-if="cart.length === 0">
                            <div class="text-center py-10">
                                <div class="text-5xl mb-3">🛒</div>
                                <p class="text-gray-400 font-bold">Keranjang Kosong</p>
                                <p class="text-gray-600 text-sm mb-4">Lapar? Yuk cari makan dulu!</p>
                                <button @click="showModal = false" class="text-brand-green font-bold border border-brand-green px-4 py-2 rounded-lg hover:bg-brand-green hover:text-black transition">Tutup</button>
                            </div>
                        </template>
                        <div class="space-y-4">
                            <template x-for="item in cart" :key="item.id">
                                <div class="bg-gray-800/50 border border-gray-700 p-4 rounded-xl flex gap-4">
                                    <img :src="item.img" class="w-16 h-16 rounded-lg object-cover bg-gray-700">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-white" x-text="item.name"></h4>
                                        <p class="text-gray-400 text-xs" x-text="item.qty + 'x @ Rp ' + formatRupiah(item.price)"></p>
                                        <!-- Show Addons in Cart -->
                                        <template x-if="item.addons && item.addons.length > 0">
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                <template x-for="ad in item.addons">
                                                    <span class="text-[10px] bg-gray-700 px-2 py-0.5 rounded text-gray-300" x-text="ad.name + ' (+' + formatRupiah(ad.price) + ')'"></span>
                                                </template>
                                            </div>
                                        </template>
                                        <p x-show="item.note" class="text-xs text-yellow-500 mt-1 italic" x-text="'Note: ' + item.note"></p>
                                    </div>
                                    <div class="flex flex-col items-end justify-between">
                                        <span class="font-bold text-brand-green" x-text="'Rp ' + formatRupiah(item.total)"></span>
                                        <button @click="removeFromCart(item.id)" class="text-red-400 text-xs hover:underline">Hapus</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>

                <!-- FOOTER MODAL (ACTIONS) -->
                <div class="p-6 border-t border-gray-700 bg-[#0F172A]" x-show="modalView !== 'merchant_detail'">
                    <!-- Tambah ke Keranjang -->
                    <button x-show="modalView === 'menu_customization'" @click="addToCart()" class="w-full bg-brand-green hover:bg-green-500 text-black font-bold py-4 rounded-xl shadow-[0_0_20px_rgba(46,204,113,0.3)] transition transform hover:scale-[1.02] flex justify-between px-6">
                        <span>Tambah Pesanan</span>
                        <span x-text="'Rp ' + formatRupiah(currentItemTotal)"></span>
                    </button>
                    <!-- Checkout -->
                    <button x-show="modalView === 'cart_detail' && cart.length > 0" @click="processCheckout()" class="w-full bg-brand-green hover:bg-green-500 text-black font-bold py-4 rounded-xl shadow-[0_0_20px_rgba(46,204,113,0.3)] transition transform hover:scale-[1.02] flex justify-between px-6">
                        <span>Checkout Sekarang</span>
                        <span x-text="'Total: Rp ' + formatRupiah(grandTotal)"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>

</body>
</html>