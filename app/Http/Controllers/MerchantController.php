<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;   // Pastikan Model Order ada
use App\Models\Review;  // Pastikan Model Review ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MerchantController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role !== 'merchant') {
            return redirect('/');
        }

        // 1. Ambil Produk
        $products = Product::where('merchant_id', $user->id)->latest()->get();

        // 2. Hitung Statistik (Realtime dari Database)
        // Catatan: Pastikan Anda sudah membuat Model Order dan Review serta migrasi tabelnya.
        // Jika belum ada tabelnya, kode ini akan error. 
        
        // Hitung Total Pendapatan (Hanya yang statusnya 'completed' atau 'paid')
        $totalRevenue = 0;
        if (class_exists(Order::class)) {
            $totalRevenue = Order::where('merchant_id', $user->id)
                ->where('status', 'completed')
                ->sum('total_price');
        }

        // Hitung Pesanan Bulan Ini
        $ordersThisMonth = 0;
        if (class_exists(Order::class)) {
            $ordersThisMonth = Order::where('merchant_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        }

        // Hitung Rating & Jumlah Ulasan
        $rating = 0;
        $reviewCount = 0;
        if (class_exists(Review::class)) {
            $rating = Review::where('merchant_id', $user->id)->avg('rating') ?? 0;
            $reviewCount = Review::where('merchant_id', $user->id)->count();
        }

        return view('merchant.dashboard', compact(
            'user', 
            'products', 
            'totalRevenue', 
            'ordersThisMonth', 
            'rating', 
            'reviewCount'
        ));
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'category' => 'nullable|string', // Tambahan
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
            'category' => $request->category, // Simpan Kategori
            // Simpan addons sebagai JSON. Pastikan kolom 'addons' ada di database atau gunakan cast di Model
            'addons' => $request->addons, 
            'image' => $imagePath,
            'is_available' => true,
        ]);

        return back()->with('success', 'Menu berhasil ditambahkan!');
    }

    public function updateProduct(Request $request, $id) 
    {
        $product = Product::where('id', $id)->where('merchant_id', Auth::id())->firstOrFail();

        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
        ]);

        // Update Data
        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->category = $request->category; // Update Kategori
        $product->addons = $request->addons;     // Update Addons
        $product->is_available = $request->has('is_available'); 

        // Ganti Gambar jika ada upload baru
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->save();

        return back()->with('success', 'Produk berhasil diperbarui!');
    }

    public function deleteProduct($id) 
    {
        $product = Product::where('id', $id)->where('merchant_id', Auth::id())->firstOrFail();
        
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        
        $product->delete();
        return back()->with('success', 'Produk dihapus.');
    }

    // --- FITUR BARU: UPDATE PROFIL WARUNG ---
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'store_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'banner' => 'nullable|image|max:2048', // Validasi Banner
        ]);

        $user->name = $request->name;
        $user->store_name = $request->store_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;

        // Logic Upload Banner
        if ($request->hasFile('banner')) {
            // Hapus banner lama jika bukan default (opsional)
            if ($user->banner) {
                Storage::disk('public')->delete($user->banner);
            }
            $user->banner = $request->file('banner')->store('banners', 'public');
        }

        $user->save();

        return back()->with('success', 'Profil warung berhasil diperbarui!');
    }
}