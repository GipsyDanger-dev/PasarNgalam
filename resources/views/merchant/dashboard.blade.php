<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitra PasarNgalam - {{ $user->store_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'brand-bg': '#0B1120', 'brand-card': '#151F32', 'brand-green': '#00E073', 'brand-text': '#94A3B8' },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    animation: { 'flash-red': 'flashRed 1s infinite' },
                    keyframes: {
                        flashRed: {
                            '0%, 100%': { opacity: '1', transform: 'scale(1)' },
                            '50%': { opacity: '0.7', transform: 'scale(1.02)' },
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .form-input { background-color: #151F32; border: 1px solid #334155; color: white; padding: 0.75rem; border-radius: 0.5rem; width: 100%; }
        .form-input:focus { outline: none; border-color: #00E073; }
        [x-cloak] { display: none !important; }
        .badge-notification {
            background-color: #EF4444; color: white;
            animation: flashRed 1s infinite;
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.7);
        }
    </style>
</head>

<body class="bg-brand-bg text-white min-h-screen"
      x-data="{ 
          activeTab: 'menu', 
          showModal: false,
          showSidebar: false,

          modalMode: 'create',
          formAction: '',
          formData: { id: null, name: '', description: '', price: '', category: 'Makanan Berat', is_available: true, imagePreview: null, addons: [] },

          pendingOrders: [],
          pendingCount: 0,
          audioPermission: false,
          notificationAudio: new Audio('https://cdn.freesound.org/previews/536/536108_11969242-lq.mp3'),
          EchoInstance: null,
          subscribedOrderIds: [],

          init() {
              this.fetchOrders();
              this.startRealtime();
          },

          enableNotification() {
              this.audioPermission = true;
              this.notificationAudio.volume = 0.01;
              this.notificationAudio.play().then(() => {
                  this.notificationAudio.pause();
                  this.notificationAudio.currentTime = 0;
                  this.notificationAudio.volume = 1.0;
              });
              this.startPolling();
          },

          startPolling() {
              this.fetchOrders();
              setInterval(() => { this.fetchOrders(); }, 5000);
          },

          fetchOrders() {
              fetch('{{ route('merchant.orders.api') }}')
                  .then(res => res.json())
                  .then(data => {
                      if (data.count > this.pendingCount && this.audioPermission) {
                          this.notificationAudio.play().catch(()=>{});
                      }
                      this.pendingCount = data.count;
                      this.pendingOrders = data.orders;
                      if (this.EchoInstance && Array.isArray(this.pendingOrders)) {
                          this.pendingOrders.forEach(o => {
                              const oid = o.id;
                              if (!this.subscribedOrderIds.includes(oid)) {
                                  this.subscribedOrderIds.push(oid);
                                  this.EchoInstance.channel('order.' + oid)
                                      .listen('.order.updated', () => {
                                          this.fetchOrders();
                                      });
                              }
                          });
                      }
                  });
          },

          formatRupiah(angka) {
              return new Intl.NumberFormat('id-ID').format(angka);
          },

          startRealtime() {
              try {
                  this.EchoInstance = new Echo({
                      broadcaster: 'reverb',
                      key: '{{ env('REVERB_APP_KEY') }}',
                      wsHost: '{{ env('REVERB_HOST', request()->getHost()) }}',
                      wsPort: {{ env('REVERB_PORT', 443) }},
                      wssPort: {{ env('REVERB_PORT', 443) }},
                      forceTLS: true,
                      enabledTransports: ['ws', 'wss'],
                  });
                  this.EchoInstance.channel('merchant.{{ $user->id }}')
                      .listen('.order.updated', () => {
                          this.fetchOrders();
                      });
              } catch (e) {
                  this.startPolling();
              }
          },

          openModal(mode, data = null) {
              this.modalMode = mode;
              this.showModal = true;
              if (mode === 'edit' && data) {
                  this.formAction = '/merchant/product/' + data.id;
                  this.formData = {
                      id: data.id,
                      name: data.name,
                      description: data.description,
                      price: data.price,
                      category: data.category,
                      is_available: data.is_available == 1,
                      imagePreview: data.image ? '/storage/' + data.image : null,
                      addons: data.addons ? JSON.parse(data.addons) : []
                  };
              } else {
                  this.formAction = '{{ route('merchant.product.store') }}';
                  this.resetForm(false);
              }
          },

          handleFileUpload(e) {
              const file = e.target.files[0];
              if (file) this.formData.imagePreview = URL.createObjectURL(file);
          },

          addAddon() { this.formData.addons.push({ name: '', price: 0 }); },
          removeAddon(i) { this.formData.addons.splice(i, 1); },

          resetForm(close = true) {
              this.formData = { id: null, name:'', description:'', price:'', category:'Makanan Berat', is_available:true, imagePreview:null, addons:[] };
              if (close) this.showModal = false;
          }
      }">

<!-- 🔔 NOTIFIKASI -->
@if(session('success'))
<div x-data="{ show:true }" x-show="show" 
     x-init="setTimeout(()=>show=false,3000)"
     class="fixed top-24 right-4 z-50 bg-brand-green text-black px-6 py-3 rounded-xl font-bold shadow-lg">
    ✅ {{ session('success') }}
</div>
@endif



<!-- NAVBAR -->
<nav class="bg-brand-bg border-b border-white/5 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">

        <!-- Left -->
        <div class="flex items-center gap-4">
            <!-- Hamburger mobile -->
            <button class="lg:hidden p-2 bg-brand-card rounded-lg" @click="showSidebar = true">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <a href="/" class="bg-brand-green text-black p-2 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>

            <h1 class="text-lg font-bold">Dashboard Mitra</h1>
        </div>

        <!-- Right -->
        <div class="flex items-center gap-3">
            <p class="text-sm font-bold hidden sm:block">{{ $user->store_name }}</p>

            <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-brand-green">
                @if($user->profile_picture)
                    <img src="{{ asset('storage/' . $user->profile_picture) }}" class="w-full h-full object-cover">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->store_name) }}&background=00E073&color=000" class="w-full h-full object-cover">
                @endif
            </div>
        </div>
    </div>
</nav>

<!-- SIDEBAR MOBILE OVERLAY -->
<div x-show="showSidebar"
     class="fixed inset-0 bg-black/50 z-40 lg:hidden"
     @click="showSidebar=false">
</div>

<!-- SIDEBAR MOBILE SLIDE -->
<!-- SIDEBAR MOBILE SLIDE -->
<div x-show="showSidebar"
     x-transition
     class="fixed top-0 left-0 h-full w-64 z-50 bg-brand-card border-r border-white/10 p-4 lg:hidden">

    <h2 class="text-xl font-bold mb-4">{{ $user->store_name }}</h2>

    <nav class="space-y-1">
        
        <!-- MENU -->
        <button @click="activeTab='menu'; showSidebar=false"
                :class="activeTab==='menu' ? 'bg-brand-green text-black' : 'text-brand-text'"
                class="w-full px-4 py-3 rounded-xl font-semibold flex items-center justify-between">
            Daftar Menu
        </button>

        <!-- PESANAN -->
        <button @click="activeTab='orders'; showSidebar=false"
                :class="activeTab==='orders' ? 'bg-brand-green text-black' : 'text-brand-text'"
                class="w-full px-4 py-3 rounded-xl font-semibold flex items-center justify-between">
            <span>Pesanan</span>

            <span x-show="pendingCount > 0"
                  class="w-6 h-6 rounded-full bg-red-500 text-[10px] flex items-center justify-center font-bold">
                <span x-text="pendingCount"></span>
            </span>
        </button>

        <!-- PROFILE -->
        <button @click="activeTab='profile'; showSidebar=false"
                :class="activeTab==='profile' ? 'bg-brand-green text-black' : 'text-brand-text'"
                class="w-full px-4 py-3 rounded-xl font-semibold flex items-center justify-between">
            Profil Warung
        </button>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="w-full mt-3 px-4 py-3 rounded-xl text-red-400">Keluar</button>
        </form>
    </nav>
</div>


<!-- DESKTOP LAYOUT -->
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- SIDEBAR DESKTOP -->
        <div class="lg:col-span-3 space-y-4 hidden lg:block">
            <div class="bg-brand-card rounded-2xl p-4 border border-white/5 sticky top-24">
                <nav class="space-y-1">
                    <button @click="activeTab='menu'"
                            :class="activeTab==='menu'?'bg-brand-green text-black':'text-brand-text'"
                            class="w-full px-4 py-3 rounded-xl font-semibold">
                        Daftar Menu
                    </button>

                       <button @click="activeTab='orders'"
                            :class="activeTab==='orders'?'bg-brand-green text-black':'text-brand-text'"
                            class="w-full px-4 py-3 rounded-xl font-semibold">
                        Pesanan
                    </button>

                    <button @click="activeTab='profile'"
                            :class="activeTab==='profile'?'bg-brand-green text-black':'text-brand-text'"
                            class="w-full px-4 py-3 rounded-xl font-semibold">
                        Profil Warung
                    </button>

                    <div class="border-t border-white/10 my-3"></div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="text-red-400 w-full px-4 py-3">Keluar</button>
                    </form>
                </nav>
            </div>
        </div>

        <!-- MAIN -->
        <div class="lg:col-span-9 space-y-6">
            @include('merchant.partials.menu')
            @include('merchant.partials.orders')
            @include('merchant.partials.profile')
        </div>

    </div>
</div>

@include('merchant.partials.modal')

</body>
</html>
