<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\OrderActivity;
use App\Models\Review; // Pastikan Model Review sudah dibuat
use App\Events\DriverLocationUpdated;
use App\Events\OrderUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    /**
     * Update lokasi driver secara realtime
     */
    public function updateLocation(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid data'], 422);
        }

        $driver = User::find(Auth::id());
        if (!$driver) return response()->json(['error' => 'Unauthenticated'], 401);

        $driver->latitude = $request->input('latitude');
        $driver->longitude = $request->input('longitude');
        $driver->save();

        // Broadcast event jika menggunakan Websocket/Pusher
        try {
            event(new DriverLocationUpdated($driver->id, $driver->latitude, $driver->longitude));
        } catch (\Exception $e) {
            // Abaikan error broadcast jika belum setup
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Ubah status Online/Offline
     */
    public function toggleStatus(Request $request)
    {
        $driver = User::find(Auth::id());
        if (!$driver) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        if ($driver->role !== 'driver') {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $status = $request->boolean('status');
        $driver->is_online = $status;
        $driver->save();
        return response()->json(['is_online' => (bool)$driver->is_online]);
    }

    /**
     * Halaman Utama Dashboard Driver
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role !== 'driver') return redirect('/');

        // 1. Ambil Order Aktif (Status berjalan)
        $activeOrder = Order::where('driver_id', $user->id)
            ->whereIn('status', ['pending', 'cooking', 'ready', 'delivery'])
            ->with('merchant')
            ->first();

        // 2. Statistik Order Hari Ini
        $todayOrders = Order::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        // 3. Total Pendapatan Bulan Ini (Untuk Tab Earnings)
        $monthEarnings = Order::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('delivery_fee');

        // 4. Total Pendapatan Semua Waktu (Dompet Tunai)
        $totalEarnings = Order::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->sum('delivery_fee');

        // 5. DATA RIWAYAT (Tab History) - 20 Terakhir
        $historyOrders = Order::where('driver_id', $user->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->with('merchant')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // 6. DATA GRAFIK 7 HARI TERAKHIR (Tab Earnings)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            $dayIncome = Order::where('driver_id', $user->id)
                ->where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('delivery_fee');

            $chartData[] = [
                'day_name' => $date->format('D'), // Mon, Tue, Wed
                'date' => $date->format('Y-m-d'),
                'total' => $dayIncome
            ];
        }

        // 7. DATA LIST HARIAN (Rincian Bawah Tab Earnings)
        $dailyLog = Order::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->select(
                DB::raw('DATE(created_at) as date'), 
                DB::raw('count(*) as count'), 
                DB::raw('sum(delivery_fee) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        // 8. DATA PERFORMA (Rating & Persentase)
        
        // A. Hitung Rating (Menggunakan helper attribute dari User model atau query manual)
        // Kita pakai cara aman query manual jika helper belum siap
        if (class_exists('App\Models\Review')) {
            $avgRating = Review::where('target_id', $user->id)->avg('rating');
            $driverRating = $avgRating ? number_format($avgRating, 1) : '5.0';
        } else {
            $driverRating = '5.0'; // Default jika tabel review belum ada
        }

        // B. Hitung Persentase Penyelesaian
        $allOrdersCount = Order::where('driver_id', $user->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->count();
        
        $cancelledCount = Order::where('driver_id', $user->id)
            ->where('status', 'cancelled')
            ->count();

        $completionRate = 100;
        $cancellationRate = 0;

        if ($allOrdersCount > 0) {
            $completionRate = round((($allOrdersCount - $cancelledCount) / $allOrdersCount) * 100);
            $cancellationRate = round(($cancelledCount / $allOrdersCount) * 100);
        }

        return view('driver.dashboard', compact(
            'user', 
            'activeOrder', 
            'todayOrders', 
            'totalEarnings', 
            'monthEarnings',
            'historyOrders', 
            'chartData', 
            'dailyLog',
            'driverRating',
            'completionRate',
            'cancellationRate'
        ));
    }

    /**
     * Menyelesaikan order (Barang sampai ke customer)
     */
    public function completeOrder($id)
    {
        $order = Order::where('id', $id)->where('driver_id', Auth::id())->first();
        if ($order) {
            $order->update(['status' => 'completed']);
        }
        try {
            event(new OrderUpdated($order->id, $order->merchant_id, $order->driver_id, $order->status));
        } catch (\Exception $e) {}
        return back()->with('success', 'Selesai! Menunggu order berikutnya...');
    }

    /**
     * Menerima order (Driver mengambil barang di merchant)
     */
    public function acceptOrder($id)
    {
        $order = Order::where('id', $id)->where('driver_id', Auth::id())->first();
        
        if (!$order) {
            return back()->with('error', 'Order tidak ditemukan atau bukan tugas Anda.');
        }

        if ($order->status !== 'ready') {
            return back()->with('error', 'Makanan belum siap. Tunggu merchant konfirmasi.');
        }

        $order->status = 'delivery';
        $order->picked_at = now();
        $order->save();

        try {
            OrderActivity::create([
                'order_id' => $order->id,
                'actor_type' => 'driver',
                'actor_id' => Auth::id(),
                'action' => 'picked_up',
                'message' => 'Driver ' . Auth::user()->name . ' mengambil pesanan.',
            ]);
        } catch (\Exception $e) {}

        try {
            event(new OrderUpdated($order->id, $order->merchant_id, $order->driver_id, $order->status));
        } catch (\Exception $e) {}

        return back()->with('success', 'Selamat mengantar! Hati-hati di jalan.');
    }
}
