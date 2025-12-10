<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitra PasarNgalam - {{ $user->store_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
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
        
        /* Style Badge Kedap Kedip */
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
          modalMode: 'create',
          formAction: '',
          formData: { id: null, name: '', description: '', price: '', category: 'Makanan Berat', is_available: true, imagePreview: null, addons: [] },
          
          // --- LOGIKA REALTIME NOTIFIKASI ---
          pendingOrders: [], // Array untuk menampung data order
          pendingCount: 0,
          audioPermission: false, // Status izin audio browser
          notificationAudio: new Audio('https://cdn.freesound.org/previews/536/536108_11969242-lq.mp3'),

          init() { 
              console.log('🚀 Dashboard Ready'); 
          },

          // 1. Fungsi Overlay: Minta izin user & mulai polling
          enableNotification() {
              this.audioPermission = true;
              
              // Trik: Mainkan suara silent sekali agar browser kasih izin autoplay selanjutnya
              this.notificationAudio.volume = 0.01;
              this.notificationAudio.play().then(() => {
                  this.notificationAudio.pause();
                  this.notificationAudio.currentTime = 0;
                  this.notificationAudio.volume = 1.0; // Reset volume normal
              }).catch(e => console.log('Audio error:', e));
              
              // Mulai ambil data
              this.startPolling();
          },

          // 2. Fungsi Polling: Jalan setiap 5 detik
          startPolling() {
              this.fetchOrders(); // Panggil sekali di awal
              setInterval(() => {
                  this.fetchOrders();
              }, 5000);
          },

          // 3. Fungsi Ambil Data dari API
          fetchOrders() {
              fetch('{{ route('merchant.orders.api') }}')
                  .then(res => res.json())
                  .then(data => {
                      // Cek apakah ada order baru (jumlah bertambah)
                      if (data.count > this.pendingCount && this.audioPermission) {
                          // Bunyikan suara!
                          this.notificationAudio.play().catch(e => console.log('Play blocked', e));
                      }
                      
                      // Update data UI tanpa refresh
                      this.pendingCount = data.count;
                      this.pendingOrders = data.orders;
                  })
                  .catch(err => console.error('Polling Error:', err));
          },

          formatRupiah(angka) {
              return new Intl.NumberFormat('id-ID').format(angka);
          },

          // ... (LOGIKA MODAL - TIDAK BERUBAH) ...
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
                      category: data.category || 'Makanan Berat',
                      is_available: data.is_available == 1,
                      imagePreview: data.image ? '/storage/' + data.image : null,
                      addons: data.addons ? (typeof data.addons === 'string' ? JSON.parse(data.addons) : data.addons) : []
                  };
              } else {
                  this.formAction = '{{ route('merchant.product.store') }}';
                  this.resetForm(false);
              }
          },
          handleFileUpload(event) {
              const file = event.target.files[0];
              if (file) this.formData.imagePreview = URL.createObjectURL(file);
          },
          addAddon() { this.formData.addons.push({ name: '', price: 0 }); },
          removeAddon(index) { this.formData.addons.splice(index, 1); },
          resetForm(closeModal = true) {
              this.formData = { id: null, name: '', description: '', price: '', category: 'Makanan Berat', is_available: true, imagePreview: null, addons: [] };
              if (closeModal) this.showModal = false;
          }
      }" x-init="init()">

    <!-- NOTIFIKASI FLASHDATA (PHP) -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="fixed top-24 right-4 z-50 bg-brand-green text-black px-6 py-3 rounded-xl font-bold shadow-lg">✅ {{ session('success') }}</div>
    @endif

    <!-- OVERLAY LAYAR PENUH (WAJIB UTK SUARA) -->
    <div x-show="!audioPermission" class="fixed inset-0 z-[100] bg-black/95 flex flex-col items-center justify-center text-center p-4 backdrop-blur-sm" x-transition>
        <div class="bg-brand-card p-8 rounded-3xl border border-brand-green/50 shadow-[0_0_50px_rgba(0,224,115,0.2)] max-w-md animate-bounce">
            <div class="text-6xl mb-4">🔔</div>
            <h2 class="text-2xl font-bold text-white mb-2">Aktifkan Mode Toko</h2>
            <p class="text-gray-400 mb-6 text-sm">Klik tombol di bawah agar notifikasi suara bisa berbunyi saat ada pesanan masuk.</p>
            <button @click="enableNotification()" class="bg-brand-green hover:bg-green-400 text-black font-bold py-4 px-10 rounded-full shadow-lg transition transform hover:scale-105 uppercase tracking-wider">
                Mulai Berjualan
            </button>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="border-b border-white/5 bg-brand-bg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ url('/') }}" class="bg-brand-green text-black p-2 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg></a>
                <h1 class="text-lg font-bold text-white">Dashboard Mitra</h1>
            </div>
            <div class="flex items-center gap-3">
                <p class="text-sm font-bold text-white hidden sm:block">{{ $user->store_name }}</p>
                <div class="w-10 h-10 rounded-full overflow-hidden bg-brand-green flex items-center justify-center border-2 border-brand-green">
                     @if($user->store_banner) <img src="{{ asset('storage/' . $user->store_banner) }}" class="w-full h-full object-cover">
                     @else <span class="text-black font-bold">{{ substr($user->store_name, 0, 2) }}</span> @endif
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- SIDEBAR -->
            <div class="lg:col-span-3 space-y-4">
                <div class="bg-brand-card rounded-2xl p-4 border border-white/5 sticky top-24">
                    <nav class="space-y-1">
                        <button @click="activeTab = 'menu'" :class="activeTab === 'menu' ? 'bg-brand-green text-black' : 'text-brand-text hover:text-white hover:bg-white/5'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all">Daftar Menu</button>
                        
                        <!-- TOMBOL PESANAN (ADA BADGE) -->
                        <button @click="activeTab = 'orders'" 
                                :class="activeTab === 'orders' ? 'bg-brand-green text-black' : 'text-brand-text hover:text-white hover:bg-white/5'" 
                                class="w-full flex items-center justify-between px-4 py-3 rounded-xl font-semibold transition-all relative">
                            <span>Pesanan</span>
                            <div x-show="pendingCount > 0" x-transition.scale class="flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-bold badge-notification">
                                <span x-text="pendingCount"></span>
                            </div>
                        </button>
                        
                        <button @click="activeTab = 'profile'" :class="activeTab === 'profile' ? 'bg-brand-green text-black' : 'text-brand-text hover:text-white hover:bg-white/5'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all">Profil Warung</button>
                        <div class="my-4 border-t border-white/5"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf <button class="w-full flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl font-semibold transition-all">Keluar</button>
                        </form>
                    </nav>
                </div>
            </div>

            <!-- MAIN CONTENT -->
            <div class="lg:col-span-9 space-y-6">
                @include('merchant.partials.menu')
                @include('merchant.partials.orders')
                @include('merchant.partials.profile')
            </div>
        </div>
    </div>

    <!-- Modal -->
    @include('merchant.partials.modal')

</body>
</html>