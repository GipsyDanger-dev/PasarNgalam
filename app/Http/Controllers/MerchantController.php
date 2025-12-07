<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MerchantController extends Controller
{
    // NEW: API untuk realtime order notifications
    public function getRecentOrders(Request $request)
    {
        $user = Auth::user();
        $lastCheck = $request->query('last_check', 0);  // timestamp dari client

        $recentOrders = Order::where('merchant_id', $user->id)
                    ->whereIn('status', ['pending', 'cooking', 'ready'])
                    ->where('created_at', '>', now()->subMinutes(5))
                    ->with(['customer', 'driver'])
                    ->orderBy('created_at', 'desc')
                    ->get();

        // Hanya return orders yang lebih baru dari last_check
        $newOrders = $recentOrders->filter(function($order) use ($lastCheck) {
            return $order->created_at->timestamp > $lastCheck;
        });

        return response()->json([
            'has_new' => count($newOrders) > 0,
            'new_orders' => $newOrders,
            'all_pending_count' => Order::where('merchant_id', $user->id)
                                       ->where('status', 'pending')
                                       ->count()
        ]);
    }

    public function index() {
        $user = Auth::user();

        if ($user->role !== 'merchant') {
            return redirect('/');
        }

        $products = Product::where('merchant_id', $user->id)->latest()->get();

        $incomingOrders = Order::where('merchant_id', $user->id)
                    ->whereIn('status', ['pending', 'cooking', 'ready'])
                    ->with('driver')
                    ->latest()
                    ->get();

        // Financial recap
        $totalRevenue = Order::where('merchant_id', $user->id)
                ->where('status', 'completed')
                ->sum('total_price');

        $revenueThisMonth = Order::where('merchant_id', $user->id)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->sum('total_price');

        $revenueToday = Order::where('merchant_id', $user->id)
                    ->where('status', 'completed')
                    ->whereDate('created_at', Carbon::today())
                    ->sum('total_price');

        // Order history (latest 50)
        $orderHistory = Order::where('merchant_id', $user->id)
                    ->with(['customer', 'driver'])
                    ->latest()
                    ->limit(50)
                    ->get();

        // Recent activities related to this merchant's orders
        $merchantOrderIds = Order::where('merchant_id', $user->id)->pluck('id');
        $recentActivities = OrderActivity::whereIn('order_id', $merchantOrderIds)
                    ->latest()
                    ->limit(20)
                    ->get();

        return view('merchant.dashboard', compact('user', 'products', 'incomingOrders', 'totalRevenue', 'revenueThisMonth', 'revenueToday', 'orderHistory', 'recentActivities'));
    }

    public function storeProduct(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'merchant_id' => Auth::id(),
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imagePath,
            'is_available' => true,
        ]);

        return back()->with('success', 'Menu berhasil ditambahkan!');
    }

    public function updateProduct(Request $request, $id) {
        $product = Product::where('id', $id)->where('merchant_id', Auth::id())->firstOrFail();

        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
        ]);

        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;

        $product->is_available = $request->has('is_available');

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->save();

        return back()->with('success', 'Menu berhasil diperbarui!');
    }

    public function deleteProduct($id) {
        $product = Product::where('id', $id)->where('merchant_id', Auth::id())->firstOrFail();
        
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        
        $product->delete();
        return back()->with('success', 'Menu dihapus.');
    }
}