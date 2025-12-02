<div x-show="activeTab === 'profile'" x-transition.opacity.duration.300ms class="space-y-6">
                    
    <!-- STATISTIK (DINAMIS DARI DATABASE) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Card 1: Total Pendapatan -->
        <div class="bg-[#151F32] border border-white/5 p-6 rounded-2xl relative overflow-hidden group shadow-lg">
            <div class="absolute right-0 top-0 w-24 h-24 bg-[#00E073]/10 rounded-bl-full -mr-4 -mt-4 transition group-hover:scale-110"></div>
            <p class="text-gray-400 text-sm font-medium">Total Pendapatan</p>
            <!-- Menggunakan number_format -->
            <h3 class="text-2xl font-bold text-white mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
            <p class="text-xs text-[#00E073] mt-2 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Data Realtime
            </p>
        </div>

        <!-- Card 2: Pesanan Bulan Ini -->
        <div class="bg-[#151F32] border border-white/5 p-6 rounded-2xl relative overflow-hidden group shadow-lg">
            <div class="absolute right-0 top-0 w-24 h-24 bg-blue-500/10 rounded-bl-full -mr-4 -mt-4 transition group-hover:scale-110"></div>
            <p class="text-gray-400 text-sm font-medium">Pesanan Bulan Ini</p>
            <h3 class="text-2xl font-bold text-white mt-1">{{ $ordersThisMonth }} Pesanan</h3>
            <p class="text-xs text-blue-400 mt-2">
                {{ now()->format('F Y') }}
            </p>
        </div>

        <!-- Card 3: Rating -->
        <div class="bg-[#151F32] border border-white/5 p-6 rounded-2xl relative overflow-hidden group shadow-lg">
            <div class="absolute right-0 top-0 w-24 h-24 bg-purple-500/10 rounded-bl-full -mr-4 -mt-4 transition group-hover:scale-110"></div>
            <p class="text-gray-400 text-sm font-medium">Rating Warung</p>
            <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($rating, 1) }} / 5.0</h3>
            <p class="text-xs text-purple-400 mt-2">Dari {{ $reviewCount }} ulasan</p>
        </div>
    </div>

    <!-- FORM PROFIL (DINAMIS & BISA UPLOAD) -->
    <div class="bg-[#151F32] border border-white/5 rounded-2xl p-8 space-y-8 shadow-xl">
        <div class="flex justify-between items-center border-b border-white/5 pb-6">
            <div>
                <h2 class="text-xl font-bold text-white">Edit Profil Warung</h2>
                <p class="text-gray-400 text-sm">Informasi ini akan dilihat oleh pelanggan.</p>
            </div>
        </div>

        <!-- Tambahkan enctype untuk upload gambar -->
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Banner Upload Area (Dinamis) -->
            <div class="relative h-48 rounded-2xl overflow-hidden group cursor-pointer border-2 border-dashed border-gray-600 hover:border-[#00E073] transition bg-[#0B1120]">
                <!-- Input File Hidden (Agar bisa diklik seluruh area) -->
                <input type="file" name="banner" class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewBanner(this)">
                
                <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 group-hover:text-[#00E073] transition z-0">
                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="text-sm font-medium">Klik untuk ganti banner</span>
                </div>

                <!-- Gambar Banner (Cek apakah ada di DB) -->
                <img id="banner-preview" 
                     src="{{ $user->banner ? asset('storage/' . $user->banner) : 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&fit=crop' }}" 
                     class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-20 transition">
            </div>

            <!-- Input Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nama Pemilik</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                        class="bg-[#0B1120] border border-[#334155] text-white text-sm rounded-lg focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3">
                </div>
                <div>
                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Email Login</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                        class="bg-[#0B1120] border border-[#334155] text-white text-sm rounded-lg focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3">
                </div>
                <div>
                    <label class="block text-[#00E073] text-xs uppercase font-bold mb-2">Nama Warung</label>
                    <input type="text" name="store_name" value="{{ old('store_name', $user->store_name) }}" 
                        class="bg-[#0B1120] border border-[#00E073]/50 text-white text-sm rounded-lg focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3">
                </div>
                <div>
                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" 
                        class="bg-[#0B1120] border border-[#334155] text-white text-sm rounded-lg focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3">
                </div>
            </div>

            <!-- Address -->
            <div>
                <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Alamat Lengkap</label>
                <textarea rows="3" name="address" 
                    class="bg-[#0B1120] border border-[#334155] text-white text-sm rounded-lg focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3 resize-none">{{ old('address', $user->address) }}</textarea>
            </div>
            
            <!-- Tombol Simpan -->
            <div class="flex justify-end pt-4 border-t border-white/5">
                <button type="submit" class="bg-[#00E073] hover:bg-[#00C062] text-black font-bold py-3 px-8 rounded-xl shadow-lg shadow-green-900/20 transition transform hover:-translate-y-1">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Script Kecil untuk Preview Banner saat upload -->
<script>
    function previewBanner(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('banner-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>