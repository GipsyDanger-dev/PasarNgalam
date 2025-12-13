<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Review; // Pastikan Model Review di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\OrderUpdated;

class OrderController extends Controller
{
    /**
     * Helper Function: Hitung Jarak (Haversine Formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * 1. Halaman Checkout
     */
    public function indexCheckout()
    {
        return view('user.checkout.index');
    }

    /**
     * 2. Proses Checkout (Simpan ke DB)
     */
    public function processCheckout(Request $request)
    {
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

        $cart = json_decode($request->cart_data, true);

        // Merchant Logic
        if (isset($cart[0]['merchant_id'])) {
            $merchantId = $cart[0]['merchant_id'];
        } else {
            $randomMerchant = User::where('role', 'merchant')->first();
            $merchantId = $randomMerchant ? $randomMerchant->id : 1;
        }
        $merchant = User::find($merchantId);

        // Koordinat Logic
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        if (empty($lat) || empty($lng)) {
            $lat = $request->input('lat_input') ?? $lat;
            $lng = $request->input('lng_input') ?? $lng;
        }

        if ($lat === null || $lng === null) {
            return back()->with('error', 'Koordinat pengiriman tidak ditemukan.');
        }

        // Ongkir Logic
        $deliveryFee = 7000; 
        if ($merchant && $merchant->latitude && $merchant->longitude) {
            $distance = $this->calculateDistance($merchant->latitude, $merchant->longitude, $lat, $lng);
            if ($distance > 5) {
                $extraKm = ceil($distance - 5);
                $deliveryFee = 7000 + ($extraKm * 1000);
            }
        }

        // Driver Assignment (Nearest < 50km)
        // SQLite tidak mengizinkan HAVING tanpa GROUP BY, jadi gunakan whereRaw + selectRaw
        $distanceExpr = "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))";
        $assignedDriver = User::select("users.*")
            ->selectRaw("$distanceExpr AS distance", [$lat, $lng, $lat])
            ->where('role', 'driver')
            ->where('is_online', true)
            ->whereRaw("$distanceExpr < 50", [$lat, $lng, $lat])
            ->orderByRaw("$distanceExpr ASC", [$lat, $lng, $lat])
            ->first();

        // Create Order
        $paymentCode = 'PAY' . strtoupper(uniqid());
        $order = Order::create([
            'customer_id' => Auth::id() ?? 1,
            'merchant_id' => $merchantId,
            'driver_id'   => $assignedDriver ? $assignedDriver->id : null,
            'delivery_address' => $request->delivery_address,
            'dest_latitude' => $lat,
            'dest_longitude' => $lng,
            'total_price' => $request->total_amount,
            'delivery_fee' => $deliveryFee,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',  
            'payment_code' => $paymentCode,
            'items' => $cart 
        ]);

        try {
            event(new OrderUpdated($order->id, $order->merchant_id, $order->driver_id, $order->status));
        } catch (\Exception $e) {}

        if ($request->payment_method === 'cod') {
            return redirect()->route('order.track', $order->id)
                           ->with('success', 'Pesanan berhasil dibuat! Siapkan uang tunai.');
        }

        return redirect()->route('order.payment', $order->id)
                       ->with('success', 'Pesanan dibuat. Silakan selesaikan pembayaran.');
    }

    /**
     * 3. Halaman Pembayaran
     */
    public function showPayment($id)
    {
        $order = Order::with('merchant')->findOrFail($id);

        if ($order->payment_status === 'paid') {
            return redirect()->route('order.track', $order->id);
        }

        return view('user.order.payment', compact('order'));
    }

    /**
     * 4. Konfirmasi Pembayaran
     */
    public function confirmPayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($request->payment_code_input !== $order->payment_code) {
            return back()->with('error', 'Kode pembayaran salah!');
        }

        $order->update(['payment_status' => 'paid']);

        try {
            event(new OrderUpdated($order->id, $order->merchant_id, $order->driver_id, $order->status));
        } catch (\Exception $e) {}

        return redirect()->route('order.track', $order->id)
                       ->with('success', 'Pembayaran berhasil dikonfirmasi!');
    }

    /**
     * 5. Halaman Tracking (+ Logic Rating Modal)
     */
    public function trackOrder($id)
    {
        $order = Order::with(['merchant', 'driver', 'reviews'])->findOrFail($id);

        $user = Auth::user();
        if ($user && $user->id !== $order->customer_id && $user->id !== $order->merchant_id && $user->id !== $order->driver_id) {
            return redirect('/')->with('error', 'Anda tidak memiliki akses.');
        }

        // Logic tampilkan Modal Rating
        // Syarat: Status Completed DAN User ini belum memberi review di order ini
        $showRatingModal = false;
        if ($order->status == 'completed') {
            $hasReviewed = $order->reviews->where('reviewer_id', $user->id)->count() > 0;
            if (!$hasReviewed) {
                $showRatingModal = true;
            }
        }

        return view('user.order.track', compact('order', 'showRatingModal'));
    }

    /**
     * 6. Submit Review (Rating)
     */
    public function submitReview(Request $request, $id)
    {
        $request->validate([
            'merchant_rating' => 'required|integer|min:1|max:5',
            'driver_rating' => 'required|integer|min:1|max:5',
        ]);

        $order = Order::findOrFail($id);
        $user = Auth::user();

        // Simpan Review Merchant
        Review::create([
            'order_id' => $order->id,
            'reviewer_id' => $user->id,
            'target_id' => $order->merchant_id,
            'rating' => $request->merchant_rating,
            'comment' => $request->merchant_comment
        ]);

        // Simpan Review Driver (Jika ada)
        if ($order->driver_id) {
            Review::create([
                'order_id' => $order->id,
                'reviewer_id' => $user->id,
                'target_id' => $order->driver_id,
                'rating' => $request->driver_rating,
                'comment' => $request->driver_comment
            ]);
        }

        return back()->with('success', 'Terima kasih atas penilaian Anda!');
    }

    /**
     * 7. Update Status (Untuk Merchant)
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        if (Auth::user()->role == 'merchant' && $order->merchant_id != Auth::id()) {
            abort(403);
        }

        $order->status = $request->status;
        $order->save();
        try {
            event(new OrderUpdated($order->id, $order->merchant_id, $order->driver_id, $order->status));
        } catch (\Exception $e) {}
        return back()->with('success', 'Status pesanan diperbarui!');
    }

    /**
     * 8. API Location Data
     */
    public function getLocationData($id)
    {
        $order = Order::with(['driver', 'merchant'])->findOrFail($id);

        return response()->json([
            'order_id' => $order->id,
            'status' => $order->status,
            'dest_latitude' => $order->dest_latitude,
            'dest_longitude' => $order->dest_longitude,
            'driver_latitude' => $order->driver?->latitude,
            'driver_longitude' => $order->driver?->longitude,
            'merchant_latitude' => $order->merchant?->latitude,
            'merchant_longitude' => $order->merchant?->longitude,
        ]);
    }
}
