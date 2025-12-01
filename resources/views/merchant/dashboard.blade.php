<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mitra PasarNgalam - Panel Warung</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-green': '#00E073',
                        'brand-dark': '#0F172A',
                        'brand-card': '#1E293B',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .form-input { 
            background-color: rgba(15, 23, 42, 0.6); 
            border: 1px solid rgba(71, 85, 105, 0.8); 
            color: white; 
            padding: 0.75rem; 
            border-radius: 0.75rem; 
            width: 100%; 
            transition: all 0.2s;
        }
        .form-input:focus { 
            outline: none; 
            border-color: #00E073; 
            box-shadow: 0 0 0 2px rgba(0, 224, 115, 0.2);
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-brand-dark text-gray-100 min-h-screen relative selection:bg-brand-green selection:text-black"
      x-data="{ 
          activeTab: 'menu', 
          showModal: false,
          modalMode: 'create',

          formData: {
              id: null,
              name: '',
              description: '',
              price: '',
              category: 'Makanan Berat',
              imagePreview: null,
              addons: [{ name: '', price: '' }]
          },

          openModal(mode, data = null) {
              this.modalMode = mode;
              this.showModal = true;
              if (mode === 'edit' && data) {
                  this.formData = {
                      id: data.id,
                      name: data.name,
                      description: data.desc,
                      price: data.raw_price,
                      category: 'Makanan Berat',
                      imagePreview: data.img,
                      addons: [{ name: 'Extra Pedas', price: 0 }, { name: 'Tambah Nasi', price: 3000 }]
                  };
              } else {
                  this.resetForm(false); 
              }
          },

          handleFileUpload(event) {
              const file = event.target.files[0];
              if (file) {
                  this.formData.imagePreview = URL.createObjectURL(file);
              }
          },

          addAddon() { this.formData.addons.push({ name: '', price: '' }); },
          removeAddon(index) { this.formData.addons.splice(index, 1); },

          resetForm(closeModal = true) {
              this.formData = {
                  id: null,
                  name: '',
                  description: '',
                  price: '',
                  category: 'Makanan Berat',
                  imagePreview: null,
                  addons: [{ name: '', price: '' }]
              };
              if (closeModal) this.showModal = false;
          }
      }">

    <!-- BACKGROUND DECORATION -->
    <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
        <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-brand-green/5 rounded-full blur-[100px]"></div>
        <div class="absolute top-[20%] right-[-10%] w-[400px] h-[400px] bg-blue-600/10 rounded-full blur-[120px]"></div>
    </div>

    <!-- NAVBAR -->
    <nav class="glass-panel sticky top-0 z-40 border-b-0 border-b-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="bg-brand-green text-black p-2 rounded-xl hover:scale-105 transition shadow-[0_0_15px_rgba(0,224,115,0.4)]">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <div>
                        <span class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                            Dashboard Mitra
                            <span class="text-[10px] bg-brand-green/20 text-brand-green px-2 py-0.5 rounded-full border border-brand-green/30">PRO</span>
                        </span>
                        <span class="text-xs block text-gray-400">Kelola Warung & Menu</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-bold text-white">Warung Bu Kris</p>
                        <p class="text-xs text-brand-green flex items-center justify-end gap-1">
                            <span class="w-2 h-2 bg-brand-green rounded-full animate-pulse"></span> Buka
                        </p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=Bu+Kris&background=00E073&color=000&bold=true" class="h-11 w-11 rounded-full border-2 border-brand-green/50 shadow-lg">
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- SIDEBAR NAVIGATION -->
            <div class="lg:col-span-1">
                <div class="glass-panel rounded-2xl p-4 sticky top-28">
                    <nav class="space-y-2">
                        <button @click="activeTab = 'menu'" 
                            :class="activeTab === 'menu' ? 'bg-brand-green text-black font-bold shadow-[0_0_15px_rgba(0,224,115,0.3)]' : 'text-gray-400 hover:text-white hover:bg-white/5'" 
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Daftar Menu
                        </button>
                        <button @click="activeTab = 'profile'" 
                            :class="activeTab === 'profile' ? 'bg-brand-green text-black font-bold shadow-[0_0_15px_rgba(0,224,115,0.3)]' : 'text-gray-400 hover:text-white hover:bg-white/5'" 
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Profil & Statistik
                        </button>
                        <div class="pt-4 mt-4 border-t border-white/10">
                            <button class="w-full flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Keluar
                            </button>
                        </div>
                    </nav>
                </div>
            </div>

            <!-- MAIN CONTENT AREA -->
            <div class="lg:col-span-3">
                
                <!-- INCLUDE TAB MENU -->
                @include('merchant.partials.menu')

                <!-- INCLUDE TAB PROFILE -->
                @include('merchant.partials.profile')

            </div>
        </div>
    </div>

    <!-- INCLUDE MODAL -->
    @include('merchant.partials.modal')

</body>
</html>