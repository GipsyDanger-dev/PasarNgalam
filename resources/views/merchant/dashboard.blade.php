<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitra PasarNgalam - {{ $user->store_name }}</title>
    
    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-bg': '#0B1120',      
                        'brand-card': '#151F32',    
                        'brand-green': '#00E073',   
                        'brand-text': '#94A3B8'     
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .form-input { 
            background-color: #1E293B; border: 1px solid #334155; color: white; 
            padding: 0.75rem; border-radius: 0.5rem; width: 100%; transition: all 0.2s;
        }
        .form-input:focus { outline: none; border-color: #00E073; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-brand-bg text-white min-h-screen"
      x-data="{ 
          activeTab: 'menu', 
          showModal: false,
          modalMode: 'create',
          
          // Realtime notification system
          notifications: [],
          lastCheckTime: Math.floor(Date.now() / 1000) - 600,  // Start checking last 10 minutes
          notificationSound: null,
          
          // Data Form Produk
          formAction: '{{ route('merchant.product.store') }}',
          formData: { id: null, name: '', description: '', price: '', is_available: true, imagePreview: null },

          // Init - setup notification polling
          init() {
              console.log('🚀 Merchant Dashboard Initialized');
              
              // Create audio element for notification sound
              this.notificationSound = new Audio('data:audio/wav;base64,UklGRiYAAABXQVZFZm10IBAAAAABAAEAQB8AAAB9AAACABAAZGF0YQIAAAAAAAA=');
              
              // Request notification permission
              if (Notification.permission === 'default') {
                  Notification.requestPermission();
              }
              
              // Check for orders immediately
              this.checkNewOrders();
              
              // Then poll every 3 seconds
              setInterval(() => this.checkNewOrders(), 3000);
              
              console.log('✅ Notification polling started');
          },

          async checkNewOrders() {
              try {
                  const url = '/api/merchant/recent-orders?last_check=' + this.lastCheckTime;
                  console.log('📡 Checking orders:', url);
                  
                  const response = await fetch(url);
                  const data = await response.json();
                  
                  console.log('📊 API Response:', data);
                  
                  if (data.has_new && data.new_orders && data.new_orders.length > 0) {
                      console.log('🎉 Found ' + data.new_orders.length + ' new orders');
                      data.new_orders.forEach(order => {
                          this.showOrderNotification(order);
                      });
                      this.lastCheckTime = Math.floor(Date.now() / 1000);
                  }
              } catch(err) {
                  console.error('❌ Notification check error:', err);
              }
          },

          showOrderNotification(order) {
              console.log('📢 Showing notification for order #' + order.id);
              
              // Play sound
              try {
                  this.playNotificationSound();
              } catch(e) {
                  console.log('Sound error:', e);
              }

              // Show notification
              const notification = {
                  id: 'notif-' + order.id,
                  orderId: order.id,
                  customerName: order.customer?.name || 'Customer',
                  total: 'Rp ' + new Intl.NumberFormat('id-ID').format(order.total_price),
                  items: order.items || '...',
                  show: true
              };
              
              this.notifications.unshift(notification);
              console.log('✅ Notification added, total:', this.notifications.length);
              
              // Auto-remove after 8 seconds
              setTimeout(() => {
                  this.notifications = this.notifications.filter(n => n.id !== notification.id);
              }, 8000);

              // Also trigger browser notification if permitted
              if (Notification.permission === 'granted') {
                  new Notification('🛵 Pesanan Baru!', {
                      body: 'Order #' + order.id + ' - ' + order.customer?.name,
                      icon: 'https://ui-avatars.com/api/?name=PasarNgalam&background=00E073&color=000&size=100',
                      tag: 'order-' + order.id,
                      requireInteraction: true
                  });
              }
          },

          playNotificationSound() {
              // Create a catchy notification sound - triple beep
              try {
                  const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                  const now = audioContext.currentTime;
                  
                  // Beep 1 - high pitch
                  const playBeep = (delay, frequency, duration = 0.25) => {
                      const osc = audioContext.createOscillator();
                      const gain = audioContext.createGain();
                      osc.connect(gain);
                      gain.connect(audioContext.destination);
                      osc.frequency.value = frequency;
                      osc.type = 'sine';
                      gain.gain.setValueAtTime(0.4, now + delay);
                      gain.gain.exponentialRampToValueAtTime(0.01, now + delay + duration);
                      osc.start(now + delay);
                      osc.stop(now + delay + duration);
                  };
                  
                  // Play three beeps in sequence
                  playBeep(0, 1200);      // First beep
                  playBeep(0.15, 1400);   // Second beep (higher)
                  playBeep(0.3, 1200);    // Third beep (back to first pitch)
              } catch(e) {
                  console.log('Sound error:', e);
              }
          },

          // Buka Modal (Create/Edit)
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
                      is_available: data.is_available == 1,
                      imagePreview: data.image ? '/storage/' + data.image : null
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

          resetForm(closeModal = true) {
              this.formData = { id: null, name: '', description: '', price: '', is_available: true, imagePreview: null };
              if (closeModal) this.showModal = false;
          }
      }" 
      x-init="init()"
      @click="if(Notification.permission === 'default') Notification.requestPermission()">

    <!-- NOTIFICATION POPUPS - FULL SCREEN MODAL -->
    <template x-for="notif in notifications" :key="notif.id">
        <div x-show="notif.show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:leave="transition ease-in duration-200"
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
            
            <div class="bg-gradient-to-b from-brand-green to-green-500 rounded-3xl shadow-2xl shadow-green-500/80 p-12 max-w-2xl w-full border-4 border-green-300 relative overflow-hidden">
                
                <!-- Background animation -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 left-0 w-40 h-40 bg-white rounded-full mix-blend-overlay blur-3xl animate-pulse"></div>
                    <div class="absolute bottom-0 right-0 w-40 h-40 bg-white rounded-full mix-blend-overlay blur-3xl animate-pulse"></div>
                </div>

                <!-- Content -->
                <div class="relative z-10 text-center">
                    <!-- Icon -->
                    <div class="text-8xl animate-bounce mb-6 inline-block">🛵</div>
                    
                    <!-- Title -->
                    <h2 class="font-black text-black text-5xl mb-4 drop-shadow-lg">🎉 PESANAN BARU!</h2>
                    
                    <!-- Order Details -->
                    <div class="bg-black/20 rounded-2xl p-8 mb-8 backdrop-blur-sm">
                        <p class="text-black text-2xl font-bold mb-3" x-text="'Order #' + notif.orderId"></p>
                        <p class="text-black/90 text-xl mb-2" x-text="notif.customerName"></p>
                        <p class="text-black text-lg font-bold" x-text="notif.total"></p>
                    </div>

                    <!-- Close button -->
                    <button @click="notif.show = false" 
                            class="bg-black/30 hover:bg-black/50 text-white font-bold py-3 px-8 rounded-2xl text-lg transition transform hover:scale-105 backdrop-blur-sm">
                        ✓ Terima Pesanan
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- NOTIFIKASI SESSION -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
         class="fixed top-24 right-4 z-50 bg-brand-green text-black px-6 py-3 rounded-xl font-bold shadow-lg transition-all">
        ✅ {{ session('success') }}
    </div>
    @endif

    <!-- HEADER ATAS (NAVBAR) -->
    <nav class="border-b border-white/5 bg-brand-bg sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <!-- Kiri: Tombol Back & Judul -->
            <div class="flex items-center gap-4">
                <a href="{{ url('/') }}" class="bg-brand-green text-black p-2 rounded-lg hover:opacity-90 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h1 class="text-lg font-bold text-white flex items-center gap-2">
                        Dashboard Mitra <span class="text-[10px] bg-white/10 text-brand-green border border-brand-green rounded px-1.5">PRO</span>
                    </h1>
                    <p class="text-xs text-brand-text">Kelola Warung & Menu</p>
                </div>
            </div>

            <!-- Kanan: Nama Warung & Status -->
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-white">{{ $user->store_name }}</p>
                    <p class="text-[10px] text-brand-green flex items-center justify-end gap-1">
                        <span class="w-1.5 h-1.5 bg-brand-green rounded-full"></span> Buka
                    </p>
                </div>
                <div class="w-10 h-10 rounded-full bg-brand-green flex items-center justify-center text-black font-bold text-sm shadow-[0_0_15px_rgba(0,224,115,0.4)]">
                    {{ substr($user->store_name, 0, 2) }}
                </div>
            </div>
        </div>
    </nav>

    <!-- CONTENT LAYOUT -->
    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- 1. SIDEBAR (KIRI) -->
            <div class="lg:col-span-3 space-y-4">
                <div class="bg-brand-card rounded-2xl p-4 border border-white/5 sticky top-24">
                    <nav class="space-y-1">
                        <!-- Menu Button -->
                        <button @click="activeTab = 'menu'" 
                            :class="activeTab === 'menu' ? 'bg-brand-green text-black' : 'text-brand-text hover:text-white hover:bg-white/5'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Daftar Menu
                        </button>

                        <!-- Orders Button (New Feature) -->
                        <button @click="activeTab = 'orders'" 
                            :class="activeTab === 'orders' ? 'bg-brand-green text-black' : 'text-brand-text hover:text-white hover:bg-white/5'"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-xl font-semibold transition-all">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                Pesanan
                            </div>
                            @if(count($incomingOrders) > 0)
                                <span class="bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full animate-pulse">{{ count($incomingOrders) }}</span>
                            @endif
                        </button>
                        
                        <!-- Profil Button -->
                        <button @click="activeTab = 'profile'" 
                            :class="activeTab === 'profile' ? 'bg-brand-green text-black' : 'text-brand-text hover:text-white hover:bg-white/5'"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Profil
                        </button>

                        <div class="my-4 border-t border-white/5"></div>

                        <!-- Logout Button -->
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="w-full flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl font-semibold transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Keluar
                            </button>
                        </form>
                    </nav>
                </div>
            </div>

            <!-- 2. MAIN CONTENT (KANAN) -->
            <div class="lg:col-span-9 space-y-6">
                
                <!-- VIEW 1: DAFTAR MENU -->
                <div x-show="activeTab === 'menu'" x-transition>
                    
                    <!-- BANNER -->
                    <div class="bg-gradient-to-r from-[#00E073] to-[#00C062] rounded-3xl p-8 mb-8 relative overflow-hidden shadow-lg">
                        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                            <div class="text-brand-bg">
                                <h2 class="text-3xl font-bold mb-2">Halo, {{ explode(' ', $user->name)[0] }}! 👋</h2>
                                <p class="opacity-90 max-w-lg text-sm leading-relaxed">
                                    Siap melayani pelanggan hari ini? Jangan lupa update stok menu agar tidak mengecewakan pembeli.
                                </p>
                            </div>
                            <button @click="openModal('create')" class="bg-white text-black px-6 py-3 rounded-full font-bold shadow-lg hover:shadow-xl hover:scale-105 transition transform flex items-center gap-2">
                                <span class="text-xl">+</span> Tambah Menu Baru
                            </button>
                        </div>
                        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
                    </div>

                    <!-- DAFTAR MENU (LIST VIEW) -->
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-white">Daftar Menu Aktif</h3>
                        <span class="text-xs text-gray-500">Total {{ count($products) }} Menu</span>
                    </div>

                    <div class="space-y-4">
                        @forelse($products as $product)
                        <div class="bg-brand-card border border-white/5 rounded-2xl p-4 flex flex-col sm:flex-row items-center gap-5 hover:border-brand-green/30 transition group">
                            <!-- Foto Produk -->
                            <div class="w-full sm:w-24 h-24 bg-[#0B1120] rounded-xl flex-shrink-0 overflow-hidden relative">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl">🥘</div>
                                @endif
                            </div>

                            <!-- Info Produk -->
                            <div class="flex-1 w-full text-center sm:text-left">
                                <h4 class="text-lg font-bold text-white mb-1">{{ $product->name }}</h4>
                                <p class="text-sm text-gray-400 line-clamp-1 mb-2">{{ $product->description }}</p>
                                <div class="flex items-center justify-center sm:justify-start gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $product->is_available ? 'bg-brand-green' : 'bg-red-500' }}"></span>
                                    <span class="text-xs text-gray-300">{{ $product->is_available ? 'Tersedia' : 'Habis' }}</span>
                                </div>
                            </div>

                            <!-- Harga & Aksi -->
                            <div class="flex flex-row sm:flex-col items-center justify-between w-full sm:w-auto gap-4 sm:gap-2">
                                <span class="text-brand-green font-bold text-lg">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                <div class="flex gap-2">
                                    <button @click="openModal('edit', {{ $product }})" class="bg-[#263345] hover:bg-white hover:text-black text-gray-300 px-4 py-1.5 rounded-lg text-xs font-bold transition">Edit</button>
                                    <form action="{{ route('merchant.product.delete', $product->id) }}" method="POST" onsubmit="return confirm('Hapus menu ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="bg-[#263345] hover:bg-red-500 hover:text-white text-gray-300 px-4 py-1.5 rounded-lg text-xs font-bold transition">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12 bg-brand-card rounded-2xl border border-dashed border-gray-700">
                            <p class="text-gray-500">Belum ada menu. Tambah sekarang!</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- VIEW 2: ORDERAN MASUK -->
                <div x-show="activeTab === 'orders'" x-transition style="display: none;">
                    <div class="bg-brand-card border border-white/5 rounded-2xl p-6">
                        <h2 class="text-2xl font-bold text-white mb-6">Rekap Keuangan & Pesanan</h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-[#0B1120] p-4 rounded-xl border border-white/5">
                                <p class="text-xs text-gray-400">Pendapatan Hari Ini</p>
                                <p class="text-2xl font-bold text-brand-green">Rp {{ number_format($revenueToday ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-[#0B1120] p-4 rounded-xl border border-white/5">
                                <p class="text-xs text-gray-400">Pendapatan Bulan Ini</p>
                                <p class="text-2xl font-bold text-white">Rp {{ number_format($revenueThisMonth ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-[#0B1120] p-4 rounded-xl border border-white/5">
                                <p class="text-xs text-gray-400">Total Pendapatan</p>
                                <p class="text-2xl font-bold text-white">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <h3 class="text-lg font-bold text-white mb-4">Pesanan Masuk</h3>

                        <div class="space-y-4">
                            @forelse($incomingOrders as $order)
                            <div class="bg-[#0B1120] border border-white/10 rounded-xl p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:border-brand-green/30 transition">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-brand-green font-bold">#ORD-{{ $order->id }}</span>
                                        @if($order->status == 'pending') <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-500/20 text-yellow-400">Baru Masuk</span>
                                        @elseif($order->status == 'cooking') <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-400">Sedang Dimasak</span>
                                        @elseif($order->status == 'ready') <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-500/20 text-green-400">Siap Diambil</span>
                                        @endif
                                    </div>
                                    <p class="text-white font-bold text-lg">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                                    <p class="text-gray-400 text-sm mt-1">Alamat: {{ $order->delivery_address }}</p>
                                    <p class="text-gray-500 text-xs mt-1">Driver: {{ $order->driver ? $order->driver->name : 'Mencari Driver...' }}</p>
                                </div>

                                <div class="flex gap-2">
                                    @if($order->status == 'pending')
                                        <form action="{{ route('merchant.order.update', $order->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="cooking">
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-lg font-bold text-sm transition shadow-lg">🍳 Masak Sekarang</button>
                                        </form>
                                    @elseif($order->status == 'cooking')
                                        <form action="{{ route('merchant.order.update', $order->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="ready">
                                            <button type="submit" class="bg-brand-green hover:bg-green-400 text-black px-5 py-2.5 rounded-lg font-bold text-sm transition shadow-lg animate-pulse">✅ Pesanan Siap</button>
                                        </form>
                                    @else
                                        <button disabled class="bg-gray-700 text-gray-400 px-5 py-2.5 rounded-lg font-bold text-sm cursor-not-allowed">Menunggu Driver</button>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12">
                                <div class="text-5xl mb-4">💤</div>
                                <h3 class="text-gray-400">Belum ada pesanan masuk.</h3>
                            </div>
                            @endforelse
                        </div>
                        
                        <!-- Order History -->
                        <div class="mt-8 border-t border-white/5 pt-6">
                            <h3 class="text-lg font-bold text-white mb-4">Riwayat Pesanan Terbaru</h3>
                            <div class="space-y-3">
                                @forelse($orderHistory as $h)
                                    <div class="bg-[#0B1120] p-4 rounded-xl border border-white/5 flex items-center justify-between">
                                        <div>
                                            <div class="text-sm text-gray-400">#ORD-{{ $h->id }} • {{ $h->created_at->format('d M Y H:i') }}</div>
                                            <div class="font-bold text-white">Rp {{ number_format($h->total_price,0,',','.') }} • {{ $h->delivery_address }}</div>
                                            <div class="text-xs text-gray-500">Pelanggan: {{ $h->customer->name ?? '-' }} • Driver: {{ $h->driver->name ?? '-' }}</div>
                                        </div>
                                        <div class="text-sm">
                                            <span class="px-3 py-1 rounded text-xs font-bold {{ $h->status == 'completed' ? 'bg-green-500/20 text-brand-green' : 'bg-gray-700 text-gray-300' }}">{{ strtoupper($h->status) }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-gray-400">Belum ada riwayat pesanan.</div>
                                @endforelse
                            </div>

                            <!-- Recent activities -->
                            @if(!empty($recentActivities) && $recentActivities->count())
                            <div class="mt-6">
                                <h4 class="text-sm text-gray-400 mb-2">Aktivitas Terbaru</h4>
                                <ul class="text-sm text-gray-300 space-y-2">
                                    @foreach($recentActivities as $act)
                                        <li class="bg-[#0B1120] p-3 rounded-lg border border-white/5">{{ $act->message }} <span class="text-xs text-gray-500">• {{ $act->created_at->diffForHumans() }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- VIEW 3: PROFILE (Form) -->
                <div x-show="activeTab === 'profile'" x-transition style="display: none;">
                    <div class="bg-brand-card border border-white/5 rounded-2xl p-8">
                        <h2 class="text-2xl font-bold text-white mb-6 pb-4 border-b border-gray-700">Edit Profil Warung</h2>
                        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                            @csrf @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div><label class="text-xs text-gray-400 block mb-2">Nama Pemilik</label><input type="text" name="name" value="{{ $user->name }}" class="form-input"></div>
                                <div><label class="text-xs text-gray-400 block mb-2">Email</label><input type="email" name="email" value="{{ $user->email }}" class="form-input"></div>
                                <div><label class="text-xs text-brand-green block mb-2">Nama Warung</label><input type="text" name="store_name" value="{{ $user->store_name }}" class="form-input border-brand-green/30"></div>
                                <div><label class="text-xs text-gray-400 block mb-2">No WhatsApp</label><input type="text" name="phone" value="{{ $user->phone }}" class="form-input"></div>
                            </div>
                            <div class="border-t border-gray-700 pt-6">
                                <label class="block text-gray-400 text-xs font-bold uppercase mb-2">Password Baru (Opsional)</label>
                                <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah" class="form-input">
                            </div>
                            <div class="flex justify-end pt-4">
                                <button type="submit" class="bg-brand-green text-black font-bold py-3 px-8 rounded-xl hover:scale-105 transition">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL (CREATE / EDIT) -->
    <div x-show="showModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" x-cloak>
        <div class="bg-brand-card rounded-2xl w-full max-w-lg border border-white/10 p-8 shadow-2xl relative" @click.away="showModal = false">
            <h3 class="text-2xl font-bold text-white mb-6" x-text="modalMode === 'edit' ? 'Edit Menu' : 'Tambah Menu'"></h3>
            
            <form :action="formAction" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <template x-if="modalMode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>

                <!-- Status Switch -->
                <div x-show="modalMode === 'edit'" class="flex items-center justify-between bg-[#0B1120] p-3 rounded-lg">
                    <span class="text-sm text-gray-300">Status Tersedia</span>
                    <label class="flex items-center gap-2 cursor-pointer relative">
                        <input type="checkbox" name="is_available" class="peer sr-only" x-model="formData.is_available">
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-green"></div>
                    </label>
                </div>

                <!-- Upload -->
                <div class="border-2 border-dashed border-gray-600 rounded-xl h-32 flex flex-col items-center justify-center relative cursor-pointer hover:border-brand-green bg-[#0B1120]">
                    <input type="file" name="image" @change="handleFileUpload" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                    <div x-show="!formData.imagePreview" class="text-center">
                        <span class="text-2xl">📸</span>
                        <p class="text-xs text-gray-400 mt-1">Upload Foto</p>
                    </div>
                    <img x-show="formData.imagePreview" :src="formData.imagePreview" class="absolute inset-0 w-full h-full object-cover rounded-xl">
                </div>

                <input type="text" name="name" x-model="formData.name" placeholder="Nama Menu" class="form-input" required>
                <div class="grid grid-cols-2 gap-4">
                    <input type="number" name="price" x-model="formData.price" placeholder="Harga (Rp)" class="form-input" required>
                    <select class="form-input appearance-none"><option>Makanan</option><option>Minuman</option></select>
                </div>
                <textarea name="description" x-model="formData.description" rows="2" placeholder="Deskripsi menu..." class="form-input"></textarea>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="flex-1 py-3 bg-gray-700 text-white rounded-xl font-bold">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-brand-green text-black rounded-xl font-bold hover:bg-green-400">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>