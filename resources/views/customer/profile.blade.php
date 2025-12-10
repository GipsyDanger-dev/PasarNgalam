<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - PasarNgalam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'brand-green': '#00E073', 'brand-dark': '#0F172A', 'brand-card': '#1E293B' },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-panel { background: rgba(30, 41, 59, 0.6); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
        /* Scrollbar custom untuk riwayat */
        .history-scroll::-webkit-scrollbar { width: 6px; }
        .history-scroll::-webkit-scrollbar-track { background: #0B1120; }
        .history-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    </style>
</head>
<body class="bg-brand-dark text-white min-h-screen">

    <!-- NAVBAR -->
    <nav class="border-b border-white/5 bg-[#0F172A] sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <h1 class="text-xl font-bold text-white">Profil Saya</h1>
            <div class="w-5"></div>
        </div>
    </nav>

    <!-- CONTENT -->
    <div class="max-w-4xl mx-auto px-4 py-8">

        <!-- NOTIF SUCCESS -->
        @if(session('success'))
        <div class="mb-6 bg-brand-green/10 border border-brand-green/30 text-brand-green px-6 py-4 rounded-2xl text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif

        <!-- 1. PROFILE CARD & STATS -->
        <div class="glass-panel rounded-3xl p-6 mb-8 border border-white/5 shadow-xl">
            <div class="flex flex-col md:flex-row gap-8 items-center md:items-start">
                <!-- Profile Picture -->
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-brand-green/20 mb-3 shadow-[0_0_20px_rgba(0,224,115,0.2)]">
                        <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=00E073&color=000&size=200' }}" 
                             class="w-full h-full object-cover">
                    </div>
                    <div class="text-center">
                        <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                        <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                    </div>
                </div>

                <!-- Info Stats (SUDAH DIPERBAIKI) -->
                <div class="flex-1 w-full grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="bg-[#0B1120] border border-white/5 rounded-2xl p-4 text-center">
                        <p class="text-2xl font-bold text-brand-green">{{ $orders_count ?? 0 }}</p>
                        <p class="text-[10px] uppercase font-bold text-gray-500 mt-1">Total Pesanan</p>
                    </div>
                    <div class="bg-[#0B1120] border border-white/5 rounded-2xl p-4 text-center">
                        <!-- Perbaikan: Menampilkan Total Pengeluaran -->
                        <p class="text-2xl font-bold text-brand-green">Rp {{ number_format($total_spent ?? 0, 0, ',', '.') }}</p>
                        <p class="text-[10px] uppercase font-bold text-gray-500 mt-1">Total Jajan</p>
                    </div>
                    <div class="col-span-2 md:col-span-1 bg-[#0B1120] border border-white/5 rounded-2xl p-4 text-center">
                        <p class="text-2xl font-bold text-brand-green">Member</p>
                        <p class="text-[10px] uppercase font-bold text-gray-500 mt-1">Status Akun</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. RIWAYAT PESANAN (BARU DITAMBAHKAN) -->
        <div class="mb-8">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                🥡 Riwayat Pesanan Terakhir
            </h2>
            
            <div class="glass-panel rounded-3xl p-1 border border-white/5 max-h-[400px] overflow-y-auto history-scroll">
                @forelse($orders as $order)
                <div class="p-4 border-b border-white/5 last:border-0 hover:bg-white/5 transition flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    
                    <!-- Info Kiri -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-800 flex items-center justify-center text-xl border border-gray-700">
                            @if($order->status == 'completed') ✅ 
                            @elseif($order->status == 'pending') ⏳ 
                            @elseif($order->status == 'delivery') 🛵 
                            @else 🍳 @endif
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-sm">{{ $order->merchant->store_name ?? 'Warung (Terhapus)' }}</h4>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                            </p>
                            <!-- List Item Singkat -->
                            @if(isset($order->items) && is_array($order->items))
                                <p class="text-[10px] text-gray-500 mt-1 line-clamp-1">
                                    {{ count($order->items) }} Item: 
                                    @foreach($order->items as $item)
                                        {{ $item['name'] }}@if(!$loop->last), @endif
                                    @endforeach
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Info Kanan & Aksi -->
                    <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                        <div class="text-right">
                            <p class="text-brand-green font-bold text-sm">Rp {{ number_format($order->total_price + $order->delivery_fee, 0, ',', '.') }}</p>
                            <span class="text-[10px] px-2 py-0.5 rounded-full uppercase font-bold
                                @if($order->status == 'completed') bg-green-500/20 text-green-400 border border-green-500/30
                                @elseif($order->status == 'pending') bg-yellow-500/20 text-yellow-400 border border-yellow-500/30
                                @else bg-blue-500/20 text-blue-400 border border-blue-500/30 @endif">
                                {{ $order->status }}
                            </span>
                        </div>
                        
                        <!-- Tombol Lacak (Jika belum completed) atau Detail (Jika completed) -->
                        <a href="{{ route('order.track', $order->id) }}" class="bg-gray-700 hover:bg-white hover:text-black text-white p-2 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-10">
                    <div class="text-4xl mb-2 grayscale opacity-50">🍽️</div>
                    <p class="text-gray-400 text-sm">Belum ada pesanan.</p>
                    <a href="{{ url('/') }}" class="text-brand-green text-xs font-bold hover:underline mt-2 inline-block">Mulai Jajan Sekarang</a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- 3. EDIT PROFILE FORM -->
        <div class="glass-panel rounded-3xl p-8 border border-white/5">
            <h2 class="text-lg font-bold text-white mb-6 pb-4 border-b border-white/10">⚙️ Pengaturan Akun</h2>
            
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf @method('PUT')

                <!-- Profile Picture Upload -->
                <div>
                    <label class="block text-gray-400 text-xs uppercase font-bold mb-3">Ganti Foto Profil</label>
                    <div class="flex items-center gap-4">
                        <div class="relative w-20 h-20 rounded-2xl overflow-hidden group cursor-pointer border-2 border-dashed border-gray-600 hover:border-brand-green transition bg-[#0B1120]">
                            <input type="file" name="profile_picture" class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewCustomerProfile(this)">
                            
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 group-hover:text-brand-green transition z-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </div>

                            <img id="customer-profile-pic-preview" 
                                 class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover:opacity-40 transition">
                        </div>
                        <p class="text-xs text-gray-500 max-w-[200px]">Klik kotak untuk mengganti foto. Max 2MB (JPG/PNG).</p>
                    </div>
                </div>

                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full bg-[#0B1120] border border-gray-600 text-white text-sm rounded-lg focus:ring-1 focus:ring-brand-green focus:border-brand-green p-3">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full bg-[#0B1120] border border-gray-600 text-white text-sm rounded-lg focus:ring-1 focus:ring-brand-green focus:border-brand-green p-3">
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">No. WhatsApp</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required
                            class="w-full bg-[#0B1120] border border-gray-600 text-white text-sm rounded-lg focus:ring-1 focus:ring-brand-green focus:border-brand-green p-3">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Password Baru <span class="text-gray-600 normal-case">(Opsional)</span></label>
                        <input type="password" name="password" placeholder="Biarkan kosong jika tetap"
                            class="w-full bg-[#0B1120] border border-gray-600 text-white text-sm rounded-lg focus:ring-1 focus:ring-brand-green focus:border-brand-green p-3">
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Alamat Utama</label>
                    <textarea name="address" rows="2"
                        class="w-full bg-[#0B1120] border border-gray-600 text-white text-sm rounded-lg focus:ring-1 focus:ring-brand-green focus:border-brand-green p-3 resize-none">{{ old('address', $user->address) }}</textarea>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end pt-4 border-t border-white/10">
                    <button type="submit" class="bg-brand-green hover:bg-green-400 text-black font-bold py-3 px-8 rounded-xl shadow-lg shadow-green-900/20 transition transform hover:-translate-y-1">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- LOGOUT SECTION -->
        <div class="mt-8 mb-20">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 text-red-400 hover:text-white py-4 rounded-xl border border-red-500/20 hover:bg-red-500/10 transition font-semibold">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Keluar Aplikasi
                </button>
            </form>
        </div>

    </div>

    <!-- PROFILE PICTURE PREVIEW SCRIPT -->
    <script>
        function previewCustomerProfile(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById('customer-profile-pic-preview');
                    img.src = e.target.result;
                    img.style.opacity = '1';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>
</html>