<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - PasarNgalam</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>
<body class="bg-brand-dark text-white min-h-screen">

    <!-- NAVBAR -->
    <nav class="border-b border-white/5 bg-[#0F172A] sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
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

        <!-- PROFILE CARD -->
        <div class="glass-panel rounded-3xl p-8 mb-8 border border-white/5">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Profile Picture -->
                <div class="flex flex-col items-center md:items-start">
                    <div class="w-24 h-24 rounded-2xl overflow-hidden border-2 border-brand-green/30 mb-4 flex-shrink-0">
                        <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=00E073&color=000&size=200' }}" 
                             class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-white">{{ $user->name }}</h2>
                        <p class="text-gray-400 text-sm">Akun Pembeli</p>
                    </div>
                </div>

                <!-- Info Stats -->
                <div class="flex-1 grid grid-cols-3 gap-4">
                    <div class="bg-[#0B1120] border border-white/5 rounded-2xl p-4 text-center">
                        <p class="text-2xl font-bold text-brand-green">{{ $orders_count ?? 0 }}</p>
                        <p class="text-xs text-gray-400 mt-1">Pesanan</p>
                    </div>
                    <div class="bg-[#0B1120] border border-white/5 rounded-2xl p-4 text-center">
                        <p class="text-2xl font-bold text-brand-green">{{ $total_spent ?? 'Rp 0' }}</p>
                        <p class="text-xs text-gray-400 mt-1">Pengeluaran</p>
                    </div>
                    <div class="bg-[#0B1120] border border-white/5 rounded-2xl p-4 text-center">
                        <p class="text-2xl font-bold text-brand-green">⭐ 4.8</p>
                        <p class="text-xs text-gray-400 mt-1">Rating</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- EDIT PROFILE FORM -->
        <div class="glass-panel rounded-3xl p-8 border border-white/5">
            <h2 class="text-2xl font-bold text-white mb-8 pb-4 border-b border-white/10">Edit Profil</h2>
            
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf @method('PUT')

                <!-- Profile Picture Upload -->
                <div>
                    <label class="block text-gray-400 text-xs uppercase font-bold mb-3">Foto Profil</label>
                    <div class="relative w-40 h-40 rounded-2xl overflow-hidden group cursor-pointer border-2 border-dashed border-gray-600 hover:border-brand-green transition bg-[#0B1120]">
                        <input type="file" name="profile_picture" class="absolute inset-0 opacity-0 cursor-pointer z-10" onchange="previewCustomerProfile(this)">
                        
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 group-hover:text-brand-green transition z-0">
                            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="text-xs font-medium">Ganti Foto</span>
                        </div>

                        <img id="customer-profile-pic-preview" 
                             src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=00E073&color=000&size=300' }}" 
                             class="absolute inset-0 w-full h-full object-cover opacity-70 group-hover:opacity-40 transition">
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
                        <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Password Baru (Opsional)</label>
                        <input type="password" name="password" placeholder="Isi untuk ganti password"
                            class="w-full bg-[#0B1120] border border-gray-600 text-white text-sm rounded-lg focus:ring-1 focus:ring-brand-green focus:border-brand-green p-3">
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-gray-400 text-xs uppercase font-bold mb-2">Alamat Lengkap</label>
                    <textarea name="address" rows="3"
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
        <div class="mt-8 glass-panel rounded-3xl p-8 border border-white/5">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 text-red-400 hover:text-white py-3 rounded-xl hover:bg-red-500/10 transition font-semibold">
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
                    document.getElementById('customer-profile-pic-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>
</html>
