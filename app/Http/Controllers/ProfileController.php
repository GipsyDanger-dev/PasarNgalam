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
        $orders_count = Order::where('customer_id', $user->id)->count();
        
        return view('customer.profile', compact('user', 'orders_count'));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:6',
            'store_name' => 'nullable|string',
            'phone' => 'required|string',
            'vehicle_plate' => 'nullable|string',
            'address' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address ?? $user->address;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Upload Profile Picture
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->profile_picture = $request->file('profile_picture')->store('profile-pictures', 'public');
        }

        // Upload Banner (untuk merchant)
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

