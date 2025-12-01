<div x-show="activeTab === 'profile'" x-transition.opacity.duration.300ms class="space-y-6">
                    
    <!-- STATISTIK (DIPINDAHKAN KE SINI) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-brand-green/10 rounded-bl-full -mr-4 -mt-4 transition group-hover:scale-110"></div>
            <p class="text-gray-400 text-sm font-medium">Total Pendapatan</p>
            <h3 class="text-2xl font-bold text-white mt-1">Rp 4.250.000</h3>
            <p class="text-xs text-brand-green mt-2 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                +12% dari kemarin
            </p>
        </div>
        <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-blue-500/10 rounded-bl-full -mr-4 -mt-4 transition group-hover:scale-110"></div>
            <p class="text-gray-400 text-sm font-medium">Pesanan Bulan Ini</p>
            <h3 class="text-2xl font-bold text-white mt-1">154 Pesanan</h3>
            <p class="text-xs text-blue-400 mt-2">Target tercapai 80%</p>
        </div>
        <div class="glass-panel p-6 rounded-2xl relative overflow-hidden group">
            <div class="absolute right-0 top-0 w-24 h-24 bg-purple-500/10 rounded-bl-full -mr-4 -mt-4 transition group-hover:scale-110"></div>
            <p class="text-gray-400 text-sm font-medium">Rating Warung</p>
            <h3 class="text-2xl font-bold text-white mt-1">4.8 / 5.0</h3>
            <p class="text-xs text-purple-400 mt-2">Dari 89 ulasan</p>
        </div>
    </div>

    <!-- FORM PROFIL -->
    <div class="glass-panel rounded-2xl p-8 space-y-8">
        <div class="flex justify-between items-center border-b border-white/10 pb-6">
            <div>
                <h2 class="text-xl font-bold text-white">Edit Profil Warung</h2>
                <p class="text-gray-400 text-sm">Informasi ini akan dilihat oleh pelanggan.</p>
            </div>
        </div>

        <form class="space-y-6">
            <div class="relative h-48 rounded-2xl overflow-hidden group cursor-pointer border-2 border-dashed border-gray-600 hover:border-brand-green transition bg-gray-800/50">
                <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 group-hover:text-brand-green transition">
                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="text-sm font-medium">Klik untuk ganti banner</span>
                </div>
                <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&fit=crop" class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-20 transition">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div><label class="block text-gray-400 text-sm font-medium mb-2">Nama Warung</label><input type="text" class="form-input" value="Warung Bu Kris"></div>
                <div><label class="block text-gray-400 text-sm font-medium mb-2">Kategori Utama</label><select class="form-input text-gray-300"><option>Masakan Jawa</option><option>Chinese Food</option></select></div>
            </div>
            <div><label class="block text-gray-400 text-sm font-medium mb-2">Alamat Lengkap</label><textarea rows="3" class="form-input">Jl. Soekarno Hatta No. 12, Lowokwaru, Malang</textarea></div>
            
            <div class="flex justify-end pt-4">
                <button type="button" class="bg-brand-green text-black font-bold py-3 px-8 rounded-xl hover:bg-green-400 shadow-lg shadow-brand-green/20 transition transform hover:-translate-y-1">Simpan Profil</button>
            </div>
        </form>
    </div>
</div>