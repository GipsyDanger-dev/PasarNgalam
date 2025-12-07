<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MerchantController extends Controller
{
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

        return view('merchant.dashboard', compact('user', 'products', 'incomingOrders'));
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