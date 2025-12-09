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
    public function getRecentOrders(Request $request)
    {
        $user = Auth::user();
        $lastCheck = $request->query('last_check', 0);

        $recentOrders = Order::where('merchant_id', $user->id)
                    ->whereIn('status', ['pending', 'cooking', 'ready'])
                    ->where('created_at', '>', now()->subMinutes(5))
                    ->with(['customer', 'driver'])
                    ->orderBy('created_at', 'desc')
                    ->get();

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

        $orderHistory = Order::where('merchant_id', $user->id)
                    ->with(['customer', 'driver'])
                    ->latest()
                    ->limit(50)
                    ->get();

        $merchantOrderIds = Order::where('merchant_id', $user->id)->pluck('id');
        $recentActivities = OrderActivity::whereIn('order_id', $merchantOrderIds)
                    ->latest()
                    ->limit(20)
                    ->get();

        return view('merchant.dashboard', compact('user', 'products', 'incomingOrders', 'totalRevenue', 'revenueThisMonth', 'revenueToday', 'orderHistory', 'recentActivities'));
    }

    // --- PERBAIKAN DI SINI (STORE) ---
    public function storeProduct(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // addons tidak perlu validasi strict karena nullable
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Decode JSON addons dari input string menjadi Array PHP
        // Agar bisa disimpan oleh Eloquent (jika model sudah dicast 'array')
        $addonsData = $request->addons ? json_decode($request->addons, true) : [];

        Product::create([
            'merchant_id' => Auth::id(),
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imagePath,
            'category' => $request->category ?? 'Makanan Berat', // Tambahkan kategori
            'is_available' => true,
            'addons' => $addonsData // <--- WAJIB: Simpan addons ke database
        ]);

        return back()->with('success', 'Menu berhasil ditambahkan!');
    }

    // --- PERBAIKAN DI SINI (UPDATE) ---
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
        $product->category = $request->category; // Update kategori

        // Update Addons
        $addonsData = $request->addons ? json_decode($request->addons, true) : [];
        $product->addons = $addonsData; // <--- WAJIB: Update addons

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

public function updateProfile(Request $request) 
    {
        $user = Auth::user();

        // -----------------------------------------------------------
        // 1. DEBUGGING: CEK APAKAH FILE SAMPAI KE SERVER?
        // -----------------------------------------------------------
        
        // Cek apakah ada input file bernama 'store_banner' tapi kosong/error
        if ($request->hasFile('store_banner') == false && $request->input('debug_mode') == 'on') {
            // Jika masuk sini, berarti file GAGAL terkirim.
            // Penyebab utama: Ukuran file melebihi batas upload_max_filesize di php.ini
            dd([
                'STATUS' => 'GAGAL: File store_banner tidak terdeteksi!',
                'Saran' => 'Coba upload file gambar yang ukurannya KECIL (di bawah 2MB).',
                'Input Lain' => $request->all(),
                'Files' => $request->allFiles() // Cek apakah ada file lain yang masuk
            ]);
        }

        // -----------------------------------------------------------
        // 2. VALIDASI
        // -----------------------------------------------------------
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'store_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            // Gunakan 'nullable' agar tidak error jika tidak upload
            'store_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // -----------------------------------------------------------
        // 3. UPDATE DATA TEXT
        // -----------------------------------------------------------
        $user->name = $request->name;
        $user->email = $request->email;
        $user->store_name = $request->store_name;
        $user->phone = $request->phone;

        // -----------------------------------------------------------
        // 4. LOGIC UPLOAD BANNER (Input: store_banner -> DB: banner)
        // -----------------------------------------------------------
        if ($request->hasFile('store_banner')) {
            try {
                // Hapus banner lama jika ada
                if ($user->banner && Storage::disk('public')->exists($user->banner)) {
                    Storage::disk('public')->delete($user->banner);
                }
                
                // Simpan banner baru
                $path = $request->file('store_banner')->store('banners', 'public');
                
                // Masukkan path ke kolom 'banner' di database
                $user->banner = $path;
                
            } catch (\Exception $e) {
                return back()->with('error', 'Error Upload Banner: ' . $e->getMessage());
            }
        }

        // -----------------------------------------------------------
        // 5. LOGIC UPLOAD PROFILE PHOTO (Input: profile_photo -> DB: profile_picture)
        // -----------------------------------------------------------
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->profile_picture = $request->file('profile_photo')->store('profiles', 'public');
        }

        // 6. Update Password
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        // 7. Simpan
        $user->save();

        return back()->with('success', 'Profil diperbarui!');
    }
}