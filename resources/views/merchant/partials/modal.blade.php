<div x-show="showModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm" x-cloak>
    <!-- Modal Container -->
    <div class="bg-[#0B1120] rounded-2xl w-full max-w-2xl border border-[#1E293B] shadow-2xl relative flex flex-col max-h-[90vh]" @click.away="showModal = false">
        
        <!-- 1. Header Modal -->
        <div class="px-6 py-5 border-b border-[#1E293B] flex justify-between items-start bg-[#0F172A] rounded-t-2xl">
            <div>
                <h3 class="text-xl font-bold text-white" x-text="modalMode === 'edit' ? 'Edit Menu' : 'Tambah Menu'"></h3>
                <p class="text-xs text-gray-400 mt-1">Lengkapi informasi menu dengan detail.</p>
            </div>
            <button @click="showModal = false" class="bg-[#1E293B] hover:bg-[#334155] text-gray-400 hover:text-white p-2 rounded-full transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <!-- 2. Body Modal (Scrollable) -->
        <div class="p-6 overflow-y-auto modal-scroll space-y-6">
            <form :action="formAction" method="POST" enctype="multipart/form-data" id="menuForm">
                @csrf
                <!-- Method Spoofing untuk Edit (PUT) -->
                <template x-if="modalMode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>

                <!-- SECTION: FOTO MAKANAN -->
                <div class="flex gap-5 items-start mb-6">
                    <!-- Preview Image Box -->
                    <div class="w-24 h-24 rounded-2xl bg-[#151F32] border border-[#334155] flex-shrink-0 overflow-hidden flex items-center justify-center relative shadow-lg group">
                        <template x-if="!formData.imagePreview">
                            <div class="text-center text-gray-500">
                                <svg class="w-8 h-8 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        </template>
                        <template x-if="formData.imagePreview">
                            <img :src="formData.imagePreview" class="w-full h-full object-cover">
                        </template>
                    </div>

                    <!-- Input File -->
                    <div class="flex-1 pt-1">
                        <label class="block text-sm font-bold text-white mb-2">Foto Makanan</label>
                        <div class="flex items-center gap-0 w-full border border-[#334155] bg-[#151F32] rounded-lg overflow-hidden h-10">
                            <label class="bg-[#00E073] hover:bg-[#00C062] text-black font-bold h-full px-4 flex items-center cursor-pointer transition text-sm whitespace-nowrap">
                                Choose File
                                <input type="file" name="image" @change="handleFileUpload" class="hidden" accept="image/*">
                            </label>
                            <span class="px-3 text-gray-500 text-sm truncate w-full">No file chosen</span>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-2">Disarankan rasio 1:1, max 2MB.</p>
                    </div>
                </div>

                <!-- SECTION: INFORMASI DASAR -->
                <div class="space-y-4">
                    <label class="text-[#00E073] text-[10px] font-bold uppercase tracking-wider block mb-1">Informasi Dasar</label>
                    
                    <input type="text" name="name" x-model="formData.name" placeholder="Nama Menu (Contoh: Paket Nasi Empal)" required 
                        class="bg-[#151F32] border border-[#334155] text-white text-sm rounded-lg focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3 placeholder-gray-500">
                    
                    <textarea name="description" x-model="formData.description" rows="3" placeholder="Deskripsi menu..." 
                        class="bg-[#151F32] border border-[#334155] text-white text-sm rounded-lg focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3 placeholder-gray-500"></textarea>
                </div>

                <!-- SECTION: HARGA & KATEGORI -->
                <div class="grid grid-cols-2 gap-5 mt-4">
                    <div>
                        <label class="text-xs text-gray-300 block mb-2">Harga (Rp)</label>
                        <input type="number" name="price" x-model="formData.price" placeholder="35000" required 
                            class="bg-[#151F32] border border-[#334155] text-white text-sm rounded-lg focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3 placeholder-gray-500">
                    </div>
                    <div>
                        <label class="text-xs text-gray-300 block mb-2">Kategori</label>
                        <div class="relative">
                            <select name="category" x-model="formData.category" 
                                class="bg-[#151F32] border border-[#334155] text-white text-sm rounded-lg focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-3 appearance-none cursor-pointer">
                                <option>Makanan Berat</option>
                                <option>Cemilan</option>
                                <option>Minuman</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AVAILABILITY SWITCH -->
                <div class="mt-4 bg-[#151F32] p-3 rounded-lg border border-[#334155] flex items-center justify-between">
                    <span class="text-sm text-gray-300">Status Menu (Tersedia / Habis)</span>
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_available" class="sr-only peer" x-model="formData.is_available">
                        <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00E073] relative"></div>
                    </label>
                </div>

                <!-- SECTION: VARIAN / ADD-ON -->
                <div class="bg-[#151F32]/50 border border-[#334155] rounded-xl p-5 mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-[#00E073] rounded-full"></span>
                            <h4 class="text-white font-bold text-sm">Varian / Add-on</h4>
                        </div>
                        <button type="button" @click="addAddon()" class="bg-[#334155] hover:bg-[#475569] text-white text-xs px-3 py-1.5 rounded flex items-center gap-1 transition">
                            + Tambah
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(addon, index) in formData.addons" :key="index">
                            <div class="flex gap-3 items-center">
                                <!-- Nama Addon -->
                                <div class="flex-grow">
                                    <input type="text" placeholder="Nama (misal: Extra Pedas)" x-model="addon.name" 
                                        class="bg-[#0B1120] border border-[#334155] text-white text-sm rounded-lg focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-2.5 placeholder-gray-600">
                                </div>
                                
                                <!-- Harga Addon -->
                                <div class="w-32 relative">
                                    <span class="absolute left-3 top-2.5 text-gray-500 text-xs">Rp</span>
                                    <input type="number" placeholder="0" x-model="addon.price" 
                                        class="bg-[#0B1120] border border-[#334155] text-white text-sm rounded-lg focus:ring-1 focus:ring-[#00E073] focus:border-[#00E073] block w-full p-2.5 pl-8 text-right placeholder-gray-600">
                                </div>
                                
                                <!-- Tombol Hapus -->
                                <button type="button" @click="removeAddon(index)" class="w-10 h-10 flex items-center justify-center bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white rounded-lg transition border border-red-500/20">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                        
                        <!-- Input Hidden untuk mengirim data addon ke backend sebagai JSON -->
                        <input type="hidden" name="addons" :value="JSON.stringify(formData.addons)">
                        
                        <template x-if="formData.addons.length === 0">
                            <p class="text-xs text-gray-500 text-center italic py-2">Belum ada varian tambahan.</p>
                        </template>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-3 ml-1">*Isi harga 0 jika varian gratis.</p>
                </div>

            </form>
        </div>

        <!-- 3. Footer Modal -->
        <div class="px-6 py-5 border-t border-[#1E293B] bg-[#0B1120] rounded-b-2xl flex gap-4">
            <button type="button" @click="showModal = false" class="flex-1 py-3 bg-[#1E293B] hover:bg-[#334155] text-white rounded-lg font-medium transition border border-[#334155] text-sm">
                Batal
            </button>
            <button type="button" onclick="document.getElementById('menuForm').submit()" class="flex-1 py-3 bg-[#00E073] hover:bg-[#00C062] text-black rounded-lg font-bold shadow-lg shadow-green-900/20 transition text-sm">
                Simpan Perubahan
            </button>
        </div>

    </div>
</div>