<div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
    <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>

    <div class="flex items-center justify-center min-h-screen p-4">
        <div x-show="showModal" 
                @click.away="resetForm()"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-10 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                class="relative bg-[#0F172A] border border-gray-700 rounded-3xl w-full max-w-2xl shadow-2xl flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="px-8 py-5 border-b border-gray-700 flex justify-between items-center bg-gray-800/30 rounded-t-3xl">
                <div>
                    <h3 class="text-xl font-bold text-white" x-text="modalMode === 'create' ? 'Tambah Menu Baru' : 'Edit Menu'"></h3>
                    <p class="text-xs text-gray-400 mt-0.5">Lengkapi informasi menu dengan detail.</p>
                </div>
                <button @click="resetForm()" class="bg-gray-700 hover:bg-gray-600 text-white p-2 rounded-full transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-8 overflow-y-auto no-scrollbar space-y-6">
                
                <!-- Upload Image -->
                <div class="flex gap-6 items-start">
                    <div class="w-28 h-28 rounded-2xl bg-gray-800 border-2 border-dashed border-gray-600 flex items-center justify-center overflow-hidden relative flex-shrink-0">
                        <template x-if="!formData.imagePreview">
                            <div class="text-center p-2">
                                <svg class="w-8 h-8 text-gray-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        </template>
                        <template x-if="formData.imagePreview">
                            <img :src="formData.imagePreview" class="w-full h-full object-cover">
                        </template>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-gray-300 mb-2">Foto Makanan</label>
                        <input type="file" @change="handleFileUpload" accept="image/*" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-brand-green file:text-black hover:file:bg-green-400 cursor-pointer border border-gray-700 rounded-xl bg-gray-800/50">
                        <p class="text-xs text-gray-500 mt-2">Disarankan rasio 1:1, max 2MB.</p>
                    </div>
                </div>

                <!-- Inputs -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-brand-green uppercase tracking-wider mb-2 ml-1">Informasi Dasar</label>
                        <input x-model="formData.name" type="text" placeholder="Nama Menu (Contoh: Nasi Goreng)" class="form-input mb-4">
                        <textarea x-model="formData.description" rows="2" placeholder="Deskripsi (Contoh: Pedas manis dengan ayam suwir)" class="form-input"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2 ml-1">Harga (Rp)</label>
                            <input x-model="formData.price" type="number" placeholder="0" class="form-input font-mono text-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2 ml-1">Kategori</label>
                            <select x-model="formData.category" class="form-input">
                                <option>Makanan Berat</option>
                                <option>Cemilan</option>
                                <option>Minuman</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Add-ons -->
                <div class="bg-gray-800/30 p-5 rounded-2xl border border-white/5">
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-sm font-bold text-white flex items-center gap-2">
                            <span class="w-1.5 h-1.5 bg-brand-green rounded-full"></span>
                            Varian / Add-on
                        </label>
                        <button @click="addAddon()" type="button" class="text-xs bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded-lg border border-gray-600 transition flex items-center gap-1">
                            + Tambah
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <template x-for="(addon, index) in formData.addons" :key="index">
                            <div class="flex gap-3 items-center animate-[fadeIn_0.3s_ease-out]">
                                <div class="flex-grow">
                                    <input x-model="addon.name" type="text" placeholder="Nama Varian" class="form-input text-sm py-2">
                                </div>
                                <div class="w-32 relative">
                                    <span class="absolute left-3 top-2 text-gray-500 text-xs mt-0.5">Rp</span>
                                    <input x-model="addon.price" type="number" placeholder="0" class="form-input text-sm py-2 pl-8 text-right">
                                </div>
                                <button @click="removeAddon(index)" class="p-2 bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <p class="text-xs text-gray-500 mt-2 ml-1" x-show="formData.addons.length > 0">*Isi harga 0 jika varian gratis.</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-5 border-t border-gray-700 bg-gray-800/30 rounded-b-3xl flex gap-4">
                <button @click="resetForm()" type="button" class="flex-1 py-3.5 bg-gray-800 hover:bg-gray-700 text-white rounded-xl font-medium transition border border-gray-600">Batal</button>
                <button type="button" @click="console.log(formData); alert('Data tersimpan! Mode: ' + modalMode)" class="flex-1 py-3.5 bg-brand-green text-black rounded-xl font-bold shadow-[0_0_20px_rgba(0,224,115,0.2)] hover:shadow-[0_0_30px_rgba(0,224,115,0.4)] transition transform hover:-translate-y-0.5">
                    <span x-text="modalMode === 'create' ? 'Simpan Menu Baru' : 'Simpan Perubahan'"></span>
                </button>
            </div>

        </div>
    </div>
</div>