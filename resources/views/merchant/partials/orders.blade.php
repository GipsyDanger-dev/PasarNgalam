<div x-show="activeTab === 'orders'" x-transition style="display: none;">
    <div class="bg-brand-card border border-white/5 rounded-2xl p-6">
        
        <h2 class="text-2xl font-bold text-white mb-6">Rekap Keuangan & Pesanan</h2>

        <!-- 1. KARTU STATISTIK KEUANGAN (DIKEMBALIKAN) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <!-- Pendapatan Hari Ini -->
            <div class="bg-[#0B1120] p-4 rounded-xl border border-white/5 relative overflow-hidden group hover:border-brand-green/30 transition">
                <div class="relative z-10">
                    <p class="text-xs text-gray-400 mb-1 uppercase tracking-wider">Pendapatan Hari Ini</p>
                    <p class="text-2xl font-bold text-brand-green">Rp {{ number_format($revenueToday ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="absolute right-0 bottom-0 opacity-10 group-hover:opacity-20 transition text-brand-green">
                    <svg class="w-16 h-16 -mr-4 -mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <!-- Pendapatan Bulan Ini -->
            <div class="bg-[#0B1120] p-4 rounded-xl border border-white/5 relative overflow-hidden group hover:border-white/20 transition">
                <div class="relative z-10">
                    <p class="text-xs text-gray-400 mb-1 uppercase tracking-wider">Pendapatan Bulan Ini</p>
                    <p class="text-2xl font-bold text-white">Rp {{ number_format($revenueThisMonth ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Total Pendapatan -->
            <div class="bg-[#0B1120] p-4 rounded-xl border border-white/5 relative overflow-hidden group hover:border-white/20 transition">
                <div class="relative z-10">
                    <p class="text-xs text-gray-400 mb-1 uppercase tracking-wider">Total Pendapatan</p>
                    <p class="text-2xl font-bold text-white">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- 2. JUDUL SECTION REALTIME -->
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-3">
            Pesanan Masuk (Realtime) 
            <span class="flex h-2.5 w-2.5 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
            </span>
        </h3>

        <!-- 3. LIST PESANAN PENDING (REALTIME VIA ALPINE) -->
        <div class="space-y-4 mb-8">
            
            <!-- Jika tidak ada pesanan pending -->
            <div x-show="pendingCount === 0" class="text-center py-8 bg-[#0B1120] rounded-xl border border-dashed border-gray-700">
                <div class="text-4xl mb-3 grayscale opacity-50">💤</div>
                <h3 class="text-gray-400 font-medium text-sm">Belum ada pesanan baru.</h3>
                <p class="text-[10px] text-gray-500 mt-1">Sistem akan otomatis berbunyi jika ada pesanan masuk.</p>
            </div>

            <!-- LOOPING ORDER PENDING -->
            <template x-for="order in pendingOrders" :key="order.id">
                <div class="bg-[#0B1120] border-2 border-red-500/50 rounded-xl p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 shadow-[0_0_20px_rgba(239,68,68,0.2)] animate-flash-red">
                    
                    <!-- Info Order -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-brand-green font-bold font-mono" x-text="'#ORD-' + order.id"></span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500 text-white animate-pulse">
                                🔔 BARU MASUK!
                            </span>
                        </div>
                        
                        <p class="text-white font-bold text-2xl mb-1" x-text="'Rp ' + formatRupiah(order.total_price)"></p>
                        <p class="text-gray-400 text-sm">Alamat: <span class="text-gray-300" x-text="order.delivery_address"></span></p>
                        
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                👤 <span x-text="order.customer ? order.customer.name : 'Pelanggan'"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="w-full md:w-auto">
                        <!-- Form Terima Pesanan -->
                        <form :action="'/merchant/order/' + order.id + '/update'" method="POST" class="w-full">
                            @csrf 
                            @method('PUT')
                            <input type="hidden" name="status" value="cooking">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white px-6 py-4 rounded-xl font-bold text-sm transition shadow-lg flex items-center justify-center gap-2 transform hover:scale-105">
                                🍳 Terima & Masak
                            </button>
                        </form>
                    </div>
                </div>
            </template>
        </div>
        
        <!-- 4. PESANAN SEDANG DIPROSES (SERVER SIDE) -->
        <!-- Menampilkan yang statusnya 'cooking' atau 'ready' -->
        @if($incomingOrders->where('status', '!=', 'pending')->count() > 0)
        <h3 class="text-lg font-bold text-white mb-4 border-t border-white/10 pt-6">Sedang Diproses</h3>
        <div class="space-y-3 mb-8">
            @foreach($incomingOrders->where('status', '!=', 'pending') as $order)
                <div class="bg-[#0B1120] p-4 rounded-xl border border-white/5 flex flex-col md:flex-row justify-between items-center gap-4 hover:border-brand-green/30 transition">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-mono text-gray-500">#ORD-{{ $order->id }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->status == 'cooking' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : 'bg-green-500/20 text-green-400 border border-green-500/30' }}">
                                {{ $order->status }}
                            </span>
                        </div>
                        <div class="font-bold text-white text-sm">Rp {{ number_format($order->total_price,0,',','.') }}</div>
                        <div class="text-xs text-gray-500">{{ $order->customer->name ?? 'Pelanggan' }}</div>
                    </div>

                    <!-- Tombol Aksi Lanjutan -->
                    <div class="flex gap-2">
                        @if($order->status == 'cooking')
                            <form action="{{ route('merchant.order.update', $order->id) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="ready">
                                <button class="bg-brand-green text-black px-4 py-2 rounded-lg text-xs font-bold hover:bg-green-400 shadow-lg animate-pulse">✅ Pesanan Siap</button>
                            </form>
                        @elseif($order->status == 'ready')
                            <button disabled class="bg-gray-700 text-gray-400 px-4 py-2 rounded-lg text-xs font-bold cursor-not-allowed border border-gray-600 flex items-center gap-1">
                                🛵 Menunggu Driver
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        <!-- 5. RIWAYAT PESANAN SELESAI (DIKEMBALIKAN) -->
        <div class="border-t border-white/10 pt-6">
            <h3 class="text-lg font-bold text-white mb-4">Riwayat Pesanan Terakhir</h3>
            <div class="space-y-3">
                @forelse($orderHistory as $h)
                    <div class="bg-[#0B1120] p-4 rounded-xl border border-white/5 flex items-center justify-between hover:bg-[#111a2e] transition">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-mono text-gray-500">#ORD-{{ $h->id }}</span>
                                <span class="text-xs text-gray-600">• {{ $h->created_at->format('d M H:i') }}</span>
                            </div>
                            <div class="font-bold text-white text-sm">Rp {{ number_format($h->total_price,0,',','.') }}</div>
                            <div class="text-xs text-gray-500 mt-0.5 line-clamp-1 w-48 md:w-auto">{{ $h->delivery_address }}</div>
                        </div>
                        <div class="text-right">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $h->status == 'completed' ? 'bg-green-900/30 text-green-400 border border-green-900/50' : 'bg-gray-800 text-gray-400 border border-gray-700' }}">
                                {{ $h->status == 'completed' ? 'Selesai' : $h->status }}
                            </span>
                            <div class="text-[10px] text-gray-600 mt-2">
                                {{ $h->driver ? 'Driver: ' . explode(' ', $h->driver->name)[0] : 'No Driver' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 text-sm py-4">Belum ada riwayat pesanan.</div>
                @endforelse
            </div>
        </div>
        
    </div>
</div>