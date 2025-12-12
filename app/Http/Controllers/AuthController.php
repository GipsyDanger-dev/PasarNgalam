<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $role = Auth::user()->role;
            if ($role === 'merchant') return redirect()->intended('/merchant/dashboard');
            if ($role === 'driver') return redirect()->intended('/driver/dashboard');

            return redirect()->intended('/');
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->with('login_errors', true)
            ->withInput();
    }

    public function register(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:user,merchant,driver',
            'phone' => 'required', // WA Wajib
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->with('register_errors', true)
                ->withInput();
        }

        // Validasi Tambahan: Merchant wajib isi lokasi
        if ($request->role === 'merchant') {
            $validatorMerchant = \Validator::make($request->all(), [
                'latitude' => 'required',
                'longitude' => 'required',
                'store_name' => 'required'
            ]);
            if ($validatorMerchant->fails()) {
                return back()
                    ->withErrors($validatorMerchant)
                    ->with('register_errors', true)
                    ->withInput();
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'store_name' => $request->role === 'merchant' ? $request->store_name : null,
            'vehicle_plate' => $request->role === 'driver' ? $request->vehicle_plate : null,
            'vehicle_type' => $request->role === 'driver' ? $request->vehicle_type : null,
            // SIMPAN KOORDINAT (Penting untuk rute!)
            'latitude' => $request->latitude ?? null,
            'longitude' => $request->longitude ?? null,
            'is_active' => true,
        ]);

        Auth::login($user);

        if ($user->role === 'merchant') return redirect('/merchant/dashboard');
        if ($user->role === 'driver') return redirect('/driver/dashboard');

        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
