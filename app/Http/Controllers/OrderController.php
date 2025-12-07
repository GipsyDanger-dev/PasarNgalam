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
        // 1. Validasi Input (Wajib Numeric untuk Koordinat)
        $request->validate([
            'cart_data' => 'required',
            'delivery_address' => 'required',
            'total_amount' => 'required',
            'latitude' => 'required|numeric',  // Wajib angka
            'longitude' => 'required|numeric', // Wajib angka
        ]);

        $cart = json_decode($request->cart_data, true);

        // 2. Logic Merchant ID (Fallback jika data lama)
        if (isset($cart[0]['merchant_id'])) {
            $merchantId = $cart[0]['merchant_id'];
        } else {
            // Ambil merchant pertama di DB sebagai cadangan (Hanya untuk dev)
            $randomMerchant = User::where('role', 'merchant')->first();
            $merchantId = $randomMerchant ? $randomMerchant->id : 1;
        }

        // 3. AMBIL KOORDINAT ASLI (Tanpa Fallback Default Malang)
        // Agar lokasi pin peta user terbaca akurat
        $lat = $request->latitude;
        $lng = $request->longitude;

        // 4. Cari Driver Terdekat (Haversine Formula)
        $assignedDriver = User::select("users.*")
            ->selectRaw("(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
            ->where('role', 'driver')
            ->where('is_online', true)
            ->having('distance', '<', 50) // Radius pencarian 50 KM
            ->orderBy('distance', 'asc')
            ->first();

        // Fallback Driver (Jika tidak ada driver dekat, ambil sembarang driver untuk demo)
        if (!$assignedDriver) {
            $assignedDriver = User::where('role', 'driver')->first();
        }

        // 5. Simpan Order & Tangkap Datanya ke variabel $order
        $order = Order::create([
            'customer_id' => Auth::id() ?? 1, // ID 1 jika guest (sebaiknya login)
            'merchant_id' => $merchantId,
            'driver_id'   => $assignedDriver ? $assignedDriver->id : null,
            'delivery_address' => $request->delivery_address,
            'dest_latitude' => $lat,  // Simpan Latitude Asli
            'dest_longitude' => $lng, // Simpan Longitude Asli
            'total_price' => $request->total_amount,
            'delivery_fee' => 5000,
            'status' => 'pending'
        ]);

        // 6. Redirect ke Halaman Tracking menggunakan ID order yang baru dibuat
        if ($assignedDriver) {
            return redirect()->route('order.track', $order->id)->with('success', 'Order Berhasil! Driver ditemukan.');
        } else {
            return redirect()->route('order.track', $order->id)->with('warning', 'Order diterima Resto. Sedang mencari driver...');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        // Security: Pastikan yang update adalah merchant pemilik order
        if (Auth::user()->role == 'merchant' && $order->merchant_id != Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $order->status = $request->status;
        $order->save();
        return back()->with('success', 'Status pesanan diperbarui!');
    }

    public function track($id)
    {
        $order = Order::with(['merchant', 'driver'])->findOrFail($id);

        // Security: Pastikan customer yang login adalah pemilik order
        if (Auth::id() !== $order->customer_id) {
            return redirect('/')->with('error', 'Anda tidak memiliki akses ke pesanan ini.');
        }

        return view('order.track', compact('order'));
    }
}