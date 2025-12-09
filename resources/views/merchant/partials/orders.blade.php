<div x-show="activeTab === 'orders'" x-transition style="display: none;">
    <div class="bg-brand-card border border-white/5 rounded-2xl p-6">
        
        <h2 class="text-2xl font-bold text-white mb-6">Rekap Keuangan & Pesanan</h2>

        <!-- 1. KARTU STATISTIK KEUANGAN -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
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

        <!-- 2. DAFTAR PESANAN MASUK (ACTIVE ORDERS) -->
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            Pesanan Masuk 
            @if(count($incomingOrders) > 0)
                <span class="bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full animate-pulse">{{ count($incomingOrders) }}</span>
            @endif
        </h3>

        <div class="space-y-4">
            @forelse($incomingOrders as $order)
            <div class="bg-[#0B1120] border border-white/10 rounded-xl p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:border-brand-green/30 transition shadow-lg">
                
                <!-- Info Order -->
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-brand-green font-bold font-mono">#ORD-{{ $order->id }}</span>
                        
                        <!-- Badge Status -->
                        @if($order->status == 'pending') 
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-yellow-400 rounded-full animate-pulse"></span> Baru Masuk
                            </span>
                        @elseif($order->status == 'cooking') 
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-pulse"></span> Sedang Dimasak
                            </span>
                        @elseif($order->status == 'ready') 
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-500/20 text-green-400 border border-green-500/30 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span> Siap Diambil
                            </span>
                        @endif
                    </div>
                    
                    <p class="text-white font-bold text-xl mb-1">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                    <p class="text-gray-400 text-sm">Alamat: <span class="text-gray-300">{{ $order->delivery_address }}</span></p>
                    
                    <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            {{ $order->customer->name ?? 'Pelanggan' }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $order->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 w-full md:w-auto">
                    @if($order->status == 'pending')
                        <form action="{{ route('merchant.order.update', $order->id) }}" method="POST" class="w-full">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="cooking">
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white px-5 py-3 rounded-xl font-bold text-sm transition shadow-lg shadow-blue-900/20 flex items-center justify-center gap-2">
                                🍳 Terima & Masak
                            </button>
                        </form>
                    @elseif($order->status == 'cooking')
                        <form action="{{ route('merchant.order.update', $order->id) }}" method="POST" class="w-full">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="ready">
                            <button type="submit" class="w-full bg-brand-green hover:bg-green-400 text-black px-5 py-3 rounded-xl font-bold text-sm transition shadow-lg shadow-green-900/20 flex items-center justify-center gap-2 animate-pulse">
                                ✅ Pesanan Siap
                            </button>
                        </form>
                    @else
                        <button disabled class="w-full bg-gray-700 text-gray-400 px-5 py-3 rounded-xl font-bold text-sm cursor-not-allowed border border-gray-600 flex items-center justify-center gap-2">
                            🛵 Menunggu Driver
                        </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-12 bg-[#0B1120] rounded-xl border border-dashed border-gray-700">
                <div class="text-5xl mb-4 grayscale opacity-50">💤</div>
                <h3 class="text-gray-400 font-medium">Belum ada pesanan aktif saat ini.</h3>
                <p class="text-xs text-gray-500 mt-1">Pesanan baru akan muncul otomatis disini.</p>
            </div>
            @endforelse
        </div>
        
        <!-- 3. RIWAYAT PESANAN (HISTORY) -->
        <div class="mt-8 border-t border-white/5 pt-6">
            <h3 class="text-lg font-bold text-white mb-4">Riwayat Pesanan Terbaru</h3>
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