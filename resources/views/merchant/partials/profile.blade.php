<div x-show="activeTab === 'profile'" x-transition.opacity.duration.300ms class="space-y-6">
    
    <!-- 1. NOTIFIKASI SUKSES -->
    @if(session('success'))
    <div class="bg-[#00E073]/10 border border-[#00E073]/30 text-[#00E073] px-6 py-4 rounded-2xl text-sm font-semibold flex items-center gap-2 animate-pulse">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <!-- 2. STATISTIK RINGKAS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card: Total Pendapatan -->
        <div class="bg-[#151F32] border border-white/5 p-6 rounded-2xl relative overflow-hidden group shadow-lg hover:border-[#00E073]/30 transition">
            <div class="absolute right-0 top-0 w-24 h-24 bg-[#00E073]/10 rounded-bl-full -mr-4 -mt-4 transition group-hover:scale-110"></div>
            <p class="text-gray-400 text-sm font-medium uppercase tracking-wider">Total Pendapatan</p>
            <h3 class="text-2xl font-bold text-white mt-1">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h3>
            <p class="text-xs text-[#00E073] mt-2 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Data Realtime
            </p>
        </div>

        <!-- Card: Pesanan Bulan Ini -->
        <div class="bg-[#151F32] border border-white/5 p-6 rounded-2xl relative overflow-hidden group shadow-lg hover:border-blue-500/30 transition">
            <div class="absolute right-0 top-0 w-24 h-24 bg-blue-500/10 rounded-bl-full -mr-4 -mt-4 transition group-hover:scale-110"></div>
            <p class="text-gray-400 text-sm font-medium uppercase tracking-wider">Pesanan Bulan Ini</p>
            <h3 class="text-2xl font-bold text-white mt-1">{{ $revenueThisMonth > 0 ? 'Aktif' : '0 Pesanan' }}</h3>
            <p class="text-xs text-blue-400 mt-2">{{ now()->format('F Y') }}</p>
        </div>

        <!-- Card: Status Toko -->
        <div class="bg-[#151F32] border border-white/5 p-6 rounded-2xl relative overflow-hidden group shadow-lg hover:border-purple-500/30 transition">
            <div class="absolute right-0 top-0 w-24 h-24 bg-purple-500/10 rounded-bl-full -mr-4 -mt-4 transition group-hover:scale-110"></div>
            <p class="text-gray-400 text-sm font-medium uppercase tracking-wider">Status Warung</p>
            <h3 class="text-2xl font-bold text-white mt-1">Buka</h3>
            <p class="text-xs text-purple-400 mt-2">Siap menerima order</p>
        </div>
    </div>

    <!-- 3. FORM EDIT PROFIL & BANNER -->
  <!-- Perhatikan bagian bannerPreview mengambil data dari $user->banner -->
<div class="bg-[#151F32] border border-white/5 rounded-2xl p-8 space-y-8 shadow-xl"
     x-data="{ 
        bannerPreview: '{{ $user->banner ? asset('storage/'.$user->banner) : null }}',
        avatarPreview: '{{ 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=00E073&color=000&size=200' }}',
        
        updatePreview(event, type) {
            const file = event.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    if(type === 'banner') this.bannerPreview = e.target.result;
                    if(type === 'avatar') this.avatarPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
     }">
        
        <div class="flex justify-between items-center border-b border-white/5 pb-6">
            <div>
                <h2 class="text-xl font-bold text-white">Edit Profil Warung</h2>
                <p class="text-gray-400 text-sm mt-1">Perbarui tampilan warung Anda agar lebih menarik.</p>
            </div>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <input type="hidden" name="debug_mode" value="on">
            <!-- A. UPLOAD BANNER (AREA BESAR) -->
            <div>
                <label class="block text-gray-400 text-xs uppercase font-bold mb-3">Banner Warung</label>
                <div class="relative h-48 rounded-2xl overflow-hidden group cursor-pointer border-2 border-dashed border-gray-600 hover:border-[#00E073] transition bg-[#0B1120] shadow-inner">
                    
                    <!-- Input File Hidden -->
                    <input type="file" name="store_banner" class="absolute inset-0 opacity-0 cursor-pointer z-20" 
                           @change="updatePreview($event, 'banner')" accept="image/*">
                    
                    <!-- Placeholder Text (Muncul jika belum ada gambar) -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 group-hover:text-[#00E073] transition z-0" x-show="!bannerPreview">
                        <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span class="text-sm font-bold">Klik untuk upload banner baru</span>
                        <span class="text-xs opacity-60 mt-1">Rekomendasi: 800x200px (Max 2MB)</span>
                    </div>

                    <!-- Image Preview -->
                   <img :src="bannerPreview" x-show="bannerPreview" 
     class="absolute inset-0 w-full h-full object-cover transition duration-500 group-hover:scale-105 z-10">
     
                    <!-- Overlay saat hover (jika ada gambar) -->
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition z-10 flex items-center justify-center" x-show="bannerPreview">
                        <span class="text-white font-bold border border-white px-4 py-2 rounded-full backdrop-blur-sm">Ganti Gambar</span>
                    </div>
                </div>
            </div>

            <!-- B. INFORMASI DATA DIRI -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Kiri: Foto Profil Kecil -->
                <div class="md:col-span-1">
                    <label class="block text-gray-400 text-xs uppercase font-bold mb-3">Foto Profil</label>
                    <div class="relative w-full aspect-square rounded-2xl overflow-hidden group cursor-pointer border-2 border-dashed border-gray-600 hover:border-[#00E073] transition bg-[#0B1120] shadow-lg">
                        <input type="file" name="profile_photo" class="absolute inset-0 opacity-0 cursor-pointer z-20" 
                               @change="updatePreview($event, 'avatar')" accept="image/*">
                        
                        <img :src="avatarPreview" class="absolute inset-0 w-full h-full object-cover z-10">
                        
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition z-20 flex flex-col items-center justify-center text-white">
                            <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span class="text-xs font-bold">Ubah</span>
                        </div>
                    </div>
                </div>

                <!-- Kanan: Input Fields -->
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 content-start">
                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Nama Pemilik <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="bg-[#0B1120] border {{ $errors->has('name') ? 'border-red-500' : 'border-[#334155]' }} text-white text-sm rounded-xl focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3.5 placeholder-gray-600 shadow-sm transition">
                    </div>
                    
                    <div>
                        <label class="block text-[#00E073] text-xs uppercase font-bold mb-2">Nama Warung <span class="text-red-400">*</span></label>
                        <input type="text" name="store_name" value="{{ old('store_name', $user->store_name) }}" required
                            class="bg-[#0B1120] border {{ $errors->has('store_name') ? 'border-red-500' : 'border-[#00E073]/50' }} text-white text-sm rounded-xl focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3.5 placeholder-gray-600 shadow-sm shadow-green-900/10 transition">
                    </div>

                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Email Login <span class="text-red-400">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="bg-[#0B1120] border {{ $errors->has('email') ? 'border-red-500' : 'border-[#334155]' }} text-white text-sm rounded-xl focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3.5 placeholder-gray-600 transition">
                    </div>

                    <div>
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">No WhatsApp <span class="text-red-400">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                            class="bg-[#0B1120] border {{ $errors->has('phone') ? 'border-red-500' : 'border-[#334155]' }} text-white text-sm rounded-xl focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3.5 placeholder-gray-600 transition">
                    </div>
                </div>
            </div>

            <!-- C. PASSWORD & SUBMIT -->
            <div class="border-t border-white/10 pt-6">
                <div class="flex flex-col md:flex-row gap-6 items-end justify-between">
                    <div class="w-full md:w-1/2">
                        <label class="block text-gray-400 text-xs font-bold uppercase mb-2">Password Baru (Opsional)</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah" 
                            class="bg-[#0B1120] border border-[#334155] text-white text-sm rounded-xl focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3.5 placeholder-gray-600 transition">
                    </div>

                    <button type="submit" class="w-full md:w-auto bg-[#00E073] hover:bg-[#00C062] text-black font-bold py-3.5 px-8 rounded-xl shadow-[0_0_15px_rgba(0,224,115,0.3)] transition transform hover:-translate-y-1 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>