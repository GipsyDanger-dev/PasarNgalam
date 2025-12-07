<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\OrderActivity;
use App\Events\DriverLocationUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DriverController extends Controller
{
    public function updateLocation(Request $request)
    {
        // Log incoming request and auth context for debugging
        try {
            \Log::debug('Driver updateLocation called', ['user_id' => Auth::id(), 'payload' => $request->all()]);
        } catch (\Exception $e) {
            // ignore logging errors
        }

        // Validate incoming coordinates and report validation errors as JSON
        try {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            \Log::warning('Driver update validation failed', ['errors' => $ve->errors(), 'payload' => $request->all()]);
            return response()->json(['errors' => $ve->errors()], 422);
        }

        $driver = Auth::user();
        if (!$driver) {
            \Log::warning('Driver update attempted without authenticated user', ['payload' => $request->all()]);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        try {
            $driver->latitude = $request->input('latitude');
            $driver->longitude = $request->input('longitude');
            $driver->save();

            // Broadcast realtime update for frontend listeners (if broadcasting is configured)
            try {
                event(new DriverLocationUpdated($driver->id, $driver->latitude, $driver->longitude));
            } catch (\Exception $e) {
                // don't break on broadcast failure; just continue
                \Log::warning('Broadcast driver location failed: ' . $e->getMessage());
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $ex) {
            \Log::error('Driver location update failed', ['message' => $ex->getMessage(), 'payload' => $request->all()]);
            $response = ['error' => 'Could not update location'];
            if (config('app.debug')) {
                $response['exception'] = $ex->getMessage();
            }
            return response()->json($response, 500);
        }
    }

    public function toggleStatus(Request $request)
    {
        $driver = User::find(Auth::id());
        $driver->is_online = $request->status;
        $driver->save();
        return response()->json(['is_online' => $driver->is_online]);
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->role !== 'driver') return redirect('/');

        $activeOrder = Order::where('driver_id', $user->id)
            ->whereIn('status', ['pending', 'cooking', 'ready', 'delivery'])
            ->with('merchant')
            ->first();

        $todayOrders = Order::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $totalEarnings = Order::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->sum('delivery_fee');

        return view('driver.dashboard', compact('user', 'activeOrder', 'todayOrders', 'totalEarnings'));
    }

    public function completeOrder($id)
    {
        $order = Order::where('id', $id)->where('driver_id', Auth::id())->first();
        if ($order) {
            $order->update(['status' => 'completed']);
        }
        return back()->with('success', 'Selesai! Menunggu order berikutnya...');
    }

    /**
     * Driver confirms pickup / that food is ready and starts delivery.
     * Only the assigned driver can confirm, and order must be in 'ready' status.
     */
    public function acceptOrder($id)
    {
        $order = Order::where('id', $id)->where('driver_id', Auth::id())->first();
        if (!$order) {
            return back()->with('error', 'Order tidak ditemukan atau bukan tugas Anda.');
        }

        // Only allow acceptance if merchant has marked the order as 'ready'
        if ($order->status !== 'ready') {
            return back()->with('error', 'Makanan belum siap. Konfirmasi hanya bisa dilakukan setelah merchant menandai sebagai siap.');
        }

        // Update status to 'delivery' (driver mulai mengantar) and set picked_at
        $order->status = 'delivery';
        $order->picked_at = now();
        $order->save();

        // Log activity (simple in-app notification)
        OrderActivity::create([
            'order_id' => $order->id,
            'actor_type' => 'driver',
            'actor_id' => Auth::id(),
            'action' => 'picked_up',
            'message' => 'Driver ' . Auth::user()->name . ' mengonfirmasi pengambilan pesanan pada ' . now()->toDateTimeString(),
        ]);

        return back()->with('success', 'Konfirmasi diterima. Silakan ambil pesanan dari warung dan lanjutkan pengantaran.');
    }
}
