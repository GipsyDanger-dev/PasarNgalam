<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pembayaran - PasarNgalam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-green': '#00E073',
                        'brand-dark': '#0F172A',
                        'brand-card': '#1E293B'
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: sans-serif; }
        .glass-panel { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); }
    </style>
</head>
<body class="bg-brand-dark text-white min-h-screen">

    <!-- NAVBAR -->
    <nav class="border-b border-gray-800 bg-[#0F172A] sticky top-0 z-40">
        <div class="max-w-3xl mx-auto px-4 h-16 flex items-center gap-4">
            <a href="{{ url('/') }}" class="text-gray-400 hover:text-white flex items-center gap-2 text-sm font-medium">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <div class="h-6 w-px bg-gray-700"></div>
            <span class="font-bold text-lg">Verifikasi Pembayaran</span>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 py-12">

        <!-- STATUS ALERT -->
        @if(session('success'))
        <div class="mb-6 bg-green-500/20 border border-green-500/50 rounded-2xl p-4 text-green-300 text-center font-bold">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-500/20 border border-red-500/50 rounded-2xl p-4 text-red-300 text-center font-bold">
            ❌ {{ session('error') }}
        </div>
        @endif

        <!-- MAIN CARD -->
        <div class="glass-panel rounded-3xl p-8 border border-[#334155]">

            <!-- HEADER -->
            <div class="text-center mb-12">
                <div class="w-20 h-20 mx-auto mb-6 bg-brand-green/20 rounded-full flex items-center justify-center text-4xl">
                    💳
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Verifikasi Pembayaran</h1>
                <p class="text-gray-400">Pesanan #{{ $order->id }} - Rp {{ number_format($order->total_price + $order->delivery_fee, 0, ',', '.') }}</p>
            </div>

            <!-- PAYMENT METHOD INFO -->
            <div class="bg-[#0B1120] rounded-2xl p-6 mb-8 border border-[#334155]">
                <h3 class="text-brand-green font-bold text-sm uppercase mb-4">Metode Pembayaran Pilihan</h3>
                
                <div class="space-y-4">
                    @if($order->payment_method === 'qris')
                        <div class="flex items-center gap-4 p-4 bg-brand-card rounded-xl border border-brand-green/30">
                            <div class="text-4xl">📲</div>
                            <div>
                                <p class="font-bold text-white">QRIS - Scan & Bayar</p>
                                <p class="text-sm text-gray-400">Scan QR code dengan aplikasi pembayaran Anda</p>
                            </div>
                        </div>
                    @elseif($order->payment_method === 'gopay')
                        <div class="flex items-center gap-4 p-4 bg-brand-card rounded-xl border border-brand-green/30">
                            <div class="text-4xl">🤖</div>
                            <div>
                                <p class="font-bold text-white">GoPay</p>
                                <p class="text-sm text-gray-400">Bayar menggunakan aplikasi GoPay Anda</p>
                            </div>
                        </div>
                    @elseif($order->payment_method === 'bank')
                        <div class="flex items-center gap-4 p-4 bg-brand-card rounded-xl border border-brand-green/30">
                            <div class="text-4xl">🏦</div>
                            <div>
                                <p class="font-bold text-white">Transfer Bank</p>
                                <p class="text-sm text-gray-400">BCA: 123456789 | Mandiri: 987654321 | BRI: 555666777</p>
                            </div>
                        </div>
                    @elseif($order->payment_method === 'cod')
                        <div class="flex items-center gap-4 p-4 bg-brand-card rounded-xl border border-brand-green/30">
                            <div class="text-4xl">💵</div>
                            <div>
                                <p class="font-bold text-white">Tunai / COD</p>
                                <p class="text-sm text-gray-400">Bayar langsung ke kurir saat pesanan tiba</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- PAYMENT CODE & INSTRUCTIONS -->
            <div class="bg-[#0B1120] rounded-2xl p-6 mb-8 border border-[#334155]">
                <h3 class="text-brand-green font-bold text-sm uppercase mb-4">Kode Pembayaran Anda</h3>
                
                <div class="bg-brand-card p-4 rounded-xl mb-6 border-2 border-dashed border-brand-green/50">
                    <div class="text-center">
                        <p class="text-gray-400 text-sm mb-2">Gunakan kode ini untuk verifikasi:</p>
                        <p class="text-3xl font-mono font-bold text-brand-green tracking-widest">{{ $order->payment_code }}</p>
                        <button onclick="navigator.clipboard.writeText('{{ $order->payment_code }}'); alert('Kode disalin!')" class="mt-3 text-xs text-gray-400 hover:text-brand-green transition">
                            📋 Salin Kode
                        </button>
                    </div>
                </div>

                <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-4 text-blue-200 text-sm space-y-2">
                    <p class="font-bold">📌 Instruksi Pembayaran:</p>
                    <ol class="list-decimal list-inside space-y-1 text-xs">
                        <li>Silakan lakukan pembayaran sesuai metode yang dipilih</li>
                        <li>Setelah pembayaran berhasil, kembali ke halaman ini</li>
                        <li>Masukkan kode pembayaran di bawah untuk verifikasi</li>
                        <li>Pesanan Anda akan langsung diproses</li>
                    </ol>
                </div>
            </div>

            <!-- VERIFICATION FORM -->
            <form action="{{ route('order.confirmPayment', $order->id) }}" method="POST" class="bg-[#0B1120] rounded-2xl p-6 border border-[#334155]">
                @csrf

                <h3 class="text-brand-green font-bold text-sm uppercase mb-4">Konfirmasi Pembayaran</h3>

                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-xl">
                    <p class="text-green-300 text-sm font-bold mb-2">✅ Sudah Membayar?</p>
                    <p class="text-gray-400 text-xs mb-4">Klik tombol di bawah untuk mengkonfirmasi pembayaran Anda. Pesanan akan langsung diproses.</p>
                    
                    <!-- Hidden input dengan payment code untuk auto-verify -->
                    <input type="hidden" name="payment_code_input" value="{{ $order->payment_code }}">
                </div>

                <button type="submit" class="w-full bg-brand-green hover:bg-green-400 text-black font-bold py-4 rounded-xl transition transform hover:scale-105">
                    ✅ Konfirmasi Pembayaran Sudah Dilakukan
                </button>
                
                <p class="text-xs text-gray-500 text-center mt-4">
                    Dengan mengklik tombol ini, Anda menyatakan pembayaran sudah dilakukan sesuai metode pilihan.
                </p>
            </form>

            <!-- ADDITIONAL INFO -->
            <div class="mt-8 p-4 bg-amber-500/10 border border-amber-500/30 rounded-xl text-amber-200 text-sm">
                <p class="font-bold mb-2">⏱️ Perhatian:</p>
                <p>Pembayaran harus dikonfirmasi dalam 30 menit. Jika belum membayar, pesanan akan dibatalkan otomatis.</p>
            </div>

            <!-- ORDER SUMMARY -->
            <div class="mt-8 pt-8 border-t border-[#334155]">
                <h3 class="text-brand-green font-bold text-sm uppercase mb-4">Ringkasan Pesanan</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Dari Warung</span>
                        <span class="text-white font-bold">{{ $order->merchant->store_name ?? 'Warung' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Alamat Pengiriman</span>
                        <span class="text-white text-right">{{ $order->delivery_address }}</span>
                    </div>
                    <div class="border-t border-[#334155] pt-3 mt-3"></div>
                    <div class="flex justify-between text-lg">
                        <span class="text-gray-400">Total</span>
                        <span class="text-brand-green font-bold">Rp {{ number_format($order->total_price + $order->delivery_fee, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- BACK BUTTON -->
        <div class="text-center mt-8">
            <a href="{{ route('home') }}" class="text-gray-400 hover:text-white text-sm transition">
                ← Kembali ke Beranda
            </a>
        </div>

    </div>

</body>
</html>
