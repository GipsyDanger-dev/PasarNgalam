<div x-show="activeTab === 'menu'" x-transition.opacity.duration.300ms class="space-y-8">
                    
    <!-- HERO BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-green to-teal-500 text-black shadow-2xl shadow-brand-green/20">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
        <div class="relative z-10 p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="text-center md:text-left">
                <h1 class="text-3xl font-extrabold mb-2 text-white drop-shadow-md">Halo, Bu Kris! 👋</h1>
                <p class="text-white/90 text-lg font-medium max-w-lg">Siap melayani pelanggan hari ini? Jangan lupa update stok menu agar tidak mengecewakan pembeli.</p>
            </div>
            <button @click="openModal('create')" class="bg-white text-brand-green font-bold py-3.5 px-8 rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition transform flex items-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Menu Baru
            </button>
        </div>
    </div>

    <!-- INFO CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="glass-panel p-4 rounded-xl border border-blue-500/30 bg-blue-500/5 hover:bg-blue-500/10 transition cursor-pointer">
            <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
            </div>
            <h4 class="font-bold text-sm text-blue-100">Ikut Promo</h4>
            <p class="text-xs text-gray-400 mt-1">Tingkatkan orderan (coming soon)</p>
        </div>
        <div class="glass-panel p-4 rounded-xl border border-purple-500/30 bg-purple-500/5 hover:bg-purple-500/10 transition cursor-pointer">
            <div class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            </div>
            <h4 class="font-bold text-sm text-purple-100">Ulasan</h4>
            <p class="text-xs text-gray-400 mt-1">4 Ulasan baru</p>
        </div>
     
     
    </div>

    <div class="space-y-4">
        <div class="flex items-center justify-between px-2">
            <h2 class="text-xl font-bold text-white">Daftar Menu Aktif</h2>
            <span class="text-sm text-gray-400">Total 3 Menu</span>
        </div>

        <!-- Menu Loop -->
        @php
            $menus = [
                ['id'=>1, 'name'=>'Paket Nasi Empal', 'price'=>'35.000', 'raw_price'=>35000, 'desc'=>'Nasi putih pulen + Empal suwir manis gurih + Sambal korek', 'img'=>'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&fit=crop'],
                ['id'=>2, 'name'=>'Es Jeruk Murni', 'price'=>'8.000', 'raw_price'=>8000, 'desc'=>'100% Jeruk peras asli tanpa tambahan pemanis buatan', 'img'=>'https://images.unsplash.com/photo-1613478223719-2ab802602423?w=400&fit=crop'],
                ['id'=>3, 'name'=>'Ayam Bakar Madu', 'price'=>'22.000', 'raw_price'=>22000, 'desc'=>'Ayam bakar dengan olesan madu dan rempah pilihan', 'img'=>'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=400&fit=crop'],
            ];
        @endphp

        @foreach($menus as $menu)
        <div class="glass-panel p-4 rounded-2xl flex flex-col md:flex-row gap-5 items-center group hover:border-brand-green/30 transition duration-300">
            <!-- Image -->
            <div class="w-full md:w-28 h-28 rounded-xl overflow-hidden relative flex-shrink-0 border border-white/10">
                <img src="{{ $menu['img'] }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
            </div>
            
            <!-- Content -->
            <div class="flex-1 text-center md:text-left w-full">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start">
                    <div>
                        <h3 class="font-bold text-white text-lg">{{ $menu['name'] }}</h3>
                        <p class="text-gray-400 text-sm mt-1 line-clamp-1">{{ $menu['desc'] }}</p>
                    </div>
                    <div class="mt-2 md:mt-0">
                        <span class="text-brand-green font-bold text-lg">Rp {{ $menu['price'] }}</span>
                    </div>
                </div>
                
                <div class="flex items-center justify-center md:justify-between mt-3 pt-3 border-t border-white/5">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full shadow-[0_0_8px_rgba(34,197,94,0.6)]"></span>
                        <span class="text-xs text-gray-300">Tersedia</span>
                    </div>
                    <div class="flex gap-2">
                        <button @click="openModal('edit', {{ json_encode($menu) }})" class="flex items-center gap-1 bg-gray-700/50 hover:bg-blue-500/20 hover:text-blue-400 text-gray-300 px-3 py-1.5 rounded-lg text-xs font-medium transition border border-transparent hover:border-blue-500/30">
                            Edit
                        </button>
                        <button class="flex items-center gap-1 bg-gray-700/50 hover:bg-red-500/20 hover:text-red-400 text-gray-300 px-3 py-1.5 rounded-lg text-xs font-medium transition border border-transparent hover:border-red-500/30">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>