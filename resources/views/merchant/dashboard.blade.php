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
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
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
        .modal-scroll::-webkit-scrollbar { width: 8px; }
        .modal-scroll::-webkit-scrollbar-track { background: #0B1120; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
</head>
<body class="bg-brand-bg text-white min-h-screen"
      x-data="{ 
          activeTab: 'menu', 
          showModal: false,
          modalMode: 'create',
          formAction: '{{ route('merchant.product.store') }}',
          formData: { id: null, name: '', description: '', price: '', category: 'Makanan Berat', is_available: true, imagePreview: null, addons: [] },

          init() { console.log('🚀 Dashboard Ready'); },

          // Logic Buka Modal (Dipanggil dari menu.blade.php)
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

    <!-- NOTIFIKASI -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="fixed top-24 right-4 z-50 bg-brand-green text-black px-6 py-3 rounded-xl font-bold shadow-lg">✅ {{ session('success') }}</div>
    @endif

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
                        <button @click="activeTab = 'orders'" :class="activeTab === 'orders' ? 'bg-brand-green text-black' : 'text-brand-text hover:text-white hover:bg-white/5'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all">Pesanan</button>
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
                <!-- Include File-File Partial -->
                @include('merchant.partials.menu')
                @include('merchant.partials.orders')
                @include('merchant.partials.profile')
            </div>
        </div>
    </div>

    <!-- Modal dipanggil di luar grid layout tapi masih dalam body x-data -->
    @include('merchant.partials.modal')

</body>
</html>