<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'cart_data' => 'required',
            'delivery_address' => 'required',
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|in:qris,gopay,bank,cod',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'lat_input' => 'nullable|numeric',
            'lng_input' => 'nullable|numeric',
        ]);

        // Decode JSON dari Frontend menjadi Array PHP
        $cart = json_decode($request->cart_data, true);

        // 2. Merchant ID Logic
        if (isset($cart[0]['merchant_id'])) {
            $merchantId = $cart[0]['merchant_id'];
        } else {
            $randomMerchant = User::where('role', 'merchant')->first();
            $merchantId = $randomMerchant ? $randomMerchant->id : 1;
        }

        // 3. Koordinat Logic
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        if (empty($lat) || empty($lng)) {
            $lat = $request->input('lat_input') ?? $lat;
            $lng = $request->input('lng_input') ?? $lng;
        }

        if ($lat === null || $lng === null) {
            return back()->with('error', 'Koordinat pengiriman tidak ditemukan. Pastikan Anda memilih lokasi pada peta.');
        }

        // 4. Cari Driver (Rumus Haversine)
        $assignedDriver = User::select("users.*")
            ->selectRaw("(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
            ->where('role', 'driver')
            ->where('is_online', true)
            ->having('distance', '<', 50)
            ->orderBy('distance', 'asc')
            ->first();

        // Fallback jika tidak ada driver
        if (!$assignedDriver) {
            // Opsional: Tetap buat order tapi driver_id null (menunggu driver online)
             $assignedDriver = null; 
        }

        // 5. Create Order Header
        $paymentCode = 'PAY' . strtoupper(uniqid());

        // --- PERBAIKAN: SIMPAN CART LANGSUNG KE KOLOM ITEMS ---
        $order = Order::create([
            'customer_id' => Auth::id() ?? 1,
            'merchant_id' => $merchantId,
            'driver_id'   => $assignedDriver ? $assignedDriver->id : null,
            'delivery_address' => $request->delivery_address,
            'dest_latitude' => $lat,
            'dest_longitude' => $lng,
            'total_price' => $request->total_amount,
            'delivery_fee' => 5000,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',  
            'payment_code' => $paymentCode,
            'items' => $cart // <--- Data keranjang disimpan di sini (Array otomatis jadi JSON)
        ]);

        // HAPUS BAGIAN FOREACH ORDERITEM YANG BIKIN ERROR
        // (Sudah tidak diperlukan karena data sudah masuk ke $order->items)

        // Redirect logic
        if ($request->payment_method === 'cod') {
            $order->update(['payment_status' => 'paid']); // Anggap lunas kalau COD (atau pending sampai bayar)
            return redirect()->route('order.track', $order->id)
                           ->with('success', 'Pesanan berhasil dibuat! Bayar tunai ke kurir saat sampai.');
        }

        return redirect()->route('order.payment', $order->id)
                       ->with('success', 'Pesanan dibuat. Silakan selesaikan pembayaran.');
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        if (Auth::user()->role == 'merchant' && $order->merchant_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $order->status = $request->status;
        $order->save();
        return back()->with('success', 'Status pesanan diperbarui!');
    }

    public function payment($id)
    {
        $order = Order::findOrFail($id);

        if ($order->payment_status === 'paid') {
            return redirect()->route('order.track', $order->id)
                           ->with('success', 'Pembayaran sudah dikonfirmasi!');
        }

        return view('order.payment', compact('order'));
    }

    public function confirmPayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'payment_code_input' => 'required|string',
        ]);

        if ($request->payment_code_input !== $order->payment_code) {
            return back()->with('error', 'Kode pembayaran salah! Periksa kembali.')
                        ->withInput();
        }

        $order->update(['payment_status' => 'paid']);

        return redirect()->route('order.track', $order->id)
                       ->with('success', 'Pembayaran berhasil dikonfirmasi! 🎉');
    }

    public function track($id)
    {
        // Perbaikan: Hapus 'items.product' dari with() karena items sekarang cuma kolom JSON, bukan relasi.
        $order = Order::with(['merchant', 'driver'])->findOrFail($id);

        $user = Auth::user();
        if ($user->id !== $order->customer_id && $user->id !== $order->merchant_id && $user->id !== $order->driver_id) {
            return redirect('/')->with('error', 'Anda tidak memiliki akses ke pesanan ini.');
        }

        return view('order.track', compact('order'));
    }

    public function getLocationData($id)
    {
        $order = Order::with(['driver', 'merchant'])->findOrFail($id);

        return response()->json([
            'order_id' => $order->id,
            'dest_latitude' => $order->dest_latitude,
            'dest_longitude' => $order->dest_longitude,
            'driver_latitude' => $order->driver?->latitude,
            'driver_longitude' => $order->driver?->longitude,
            'merchant_latitude' => $order->merchant?->latitude,
            'merchant_longitude' => $order->merchant?->longitude,
            'status' => $order->status,
        ]);
    }
}