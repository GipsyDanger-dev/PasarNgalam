<div x-show="activeTab === 'menu'" x-transition.opacity.duration.300ms class="space-y-8">
    
    <!-- HERO BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-brand-green to-teal-500 text-black shadow-2xl shadow-brand-green/20">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
        <div class="relative z-10 p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="text-center md:text-left">
                <h1 class="text-3xl font-extrabold mb-2 text-white drop-shadow-md">
                    Halo, {{ explode(' ', Auth::user()->name)[0] }}! 👋
                </h1>
                <p class="text-white/90 text-lg font-medium max-w-lg">
                    Siap melayani pelanggan hari ini? Jangan lupa update stok menu.
                </p>
            </div>
            <button @click="openModal('create')" class="bg-white text-brand-green font-bold py-3.5 px-8 rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition transform flex items-center gap-2 whitespace-nowrap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Menu Baru
            </button>
        </div>
    </div>

    <!-- INFO CARDS -->
    <!-- <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="glass-panel p-4 rounded-xl border border-blue-500/30 bg-blue-500/5">
            <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <h4 class="font-bold text-sm text-blue-100">Status</h4>
            <p class="text-xs text-gray-400 mt-1">Warung Aktif</p>
        </div>
        <div class="glass-panel p-4 rounded-xl border border-purple-500/30 bg-purple-500/5">
            <div class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
            </div>
            <h4 class="font-bold text-sm text-purple-100">Rating</h4>
            <p class="text-xs text-gray-400 mt-1">4.8 / 5.0</p>
        </div>
    </div> -->

    <!-- LIST MENU -->
    <div class="space-y-4">
        <div class="flex items-center justify-between px-2">
            <h2 class="text-xl font-bold text-white">Daftar Menu Aktif</h2>
            <span class="text-sm text-gray-400">Total {{ isset($products) ? count($products) : 0 }} Menu</span>
        </div>

        @if(isset($products) && count($products) > 0)
            @foreach($products as $product)
            <div class="glass-panel p-4 rounded-2xl flex flex-col md:flex-row gap-5 items-center group hover:border-brand-green/30 transition duration-300 bg-[#151F32] border border-white/5">
                
                <!-- Image -->
                <div class="w-full md:w-28 h-28 rounded-xl overflow-hidden relative flex-shrink-0 border border-white/10 bg-black">
                    <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://placehold.co/400x400?text=No+Image' }}" 
                         class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                </div>
                
                <!-- Content -->
                <div class="flex-1 text-center md:text-left w-full">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-start">
                        <div>
                            <h3 class="font-bold text-white text-lg">{{ $product->name }}</h3>
                            <p class="text-gray-400 text-sm mt-1 line-clamp-1">{{ $product->description }}</p>
                            <span class="inline-block mt-2 text-[10px] px-2 py-0.5 rounded bg-gray-700 text-gray-300 border border-gray-600">
                                {{ $product->category ?? 'Makanan' }}
                            </span>
                        </div>
                        <div class="mt-2 md:mt-0">
                            <span class="text-brand-green font-bold text-lg">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-center md:justify-between mt-3 pt-3 border-t border-white/5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $product->is_available ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]' : 'bg-red-500' }}"></span>
                            <span class="text-xs text-gray-300">{{ $product->is_available ? 'Tersedia' : 'Habis' }}</span>
                        </div>
                        <div class="flex gap-2">
                            <!-- Tombol Edit (Pakai json_encode agar aman) -->
                            <button @click="openModal('edit', {{ json_encode($product) }})" class="flex items-center gap-1 bg-gray-700/50 hover:bg-blue-500/20 hover:text-blue-400 text-gray-300 px-3 py-1.5 rounded-lg text-xs font-medium transition border border-transparent hover:border-blue-500/30">
                                Edit
                            </button>
                            
                            <!-- Tombol Hapus -->
                            <form action="{{ route('merchant.product.delete', $product->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus menu ini?')">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="flex items-center gap-1 bg-gray-700/50 hover:bg-red-500/20 hover:text-red-400 text-gray-300 px-3 py-1.5 rounded-lg text-xs font-medium transition border border-transparent hover:border-red-500/30">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <!-- State Kosong -->
            <div class="text-center py-12 bg-[#151F32] rounded-2xl border border-dashed border-gray-700">
                <div class="text-5xl mb-4 opacity-50 grayscale">🍲</div>
                <h3 class="text-gray-300 font-bold mb-1">Belum ada menu</h3>
                <p class="text-gray-500 text-sm mb-4">Tambahkan menu pertamamu sekarang!</p>
                <button @click="openModal('create')" class="text-brand-green text-sm hover:underline">
                    + Tambah Menu
                </button>
            </div>
        @endif
    </div>
</div>