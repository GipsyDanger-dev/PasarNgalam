<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        // 1. Ambil Data Order (Urutkan dari yang terbaru)
        // Kita load 'merchant' agar nama warung bisa muncul di riwayat
        $orders = Order::where('customer_id', $user->id)
                    ->with('merchant') 
                    ->latest()
                    ->get();
        
        // 2. Hitung Jumlah Order
        $orders_count = $orders->count();

        // 3. Hitung Total Pengeluaran (Hanya yang statusnya 'completed' / Selesai)
        // Jika ingin menghitung semua termasuk yang pending, hapus ->where(...)
        $total_spent = $orders->where('status', 'completed')->sum(function($order) {
            return $order->total_price + $order->delivery_fee;
        });
        
        return view('customer.profile', compact('user', 'orders_count', 'total_spent', 'orders'));
    }

    // ... method update() biarkan tetap sama seperti sebelumnya ...
    public function update(Request $request)
    {
        // ... (Kode update Anda yang lama) ...
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // ... dst ...
        // Copy paste isi function update dari kode sebelumnya di sini
        // (Saya skip menulis ulang function update agar jawaban tidak terlalu panjang, karena fokus kita di show)
        
        // --- LOGIC UPDATE LAMA ANDA ---
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:6',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        if ($user->role === 'merchant') {
            $validationRules['store_name'] = 'required|string|max:255';
            $validationRules['banner'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        } elseif ($user->role === 'driver') {
            $validationRules['vehicle_plate'] = 'required|string|max:20';
        }

        $request->validate($validationRules);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->profile_picture = $request->file('profile_picture')->store('profile-pictures', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($user->banner) {
                Storage::disk('public')->delete($user->banner);
            }
            $user->banner = $request->file('banner')->store('banners', 'public');
        }

        if ($user->role === 'merchant') {
            $user->store_name = $request->store_name;
        } elseif ($user->role === 'driver') {
            $user->vehicle_plate = $request->vehicle_plate;
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}