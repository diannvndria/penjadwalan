<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Redirect root URL to login page.
     */
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Menampilkan form login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard'); // Arahkan ke dashboard jika sudah login
        }

        // Auto-login untuk development jika DISABLE_LOGIN diaktifkan
        if (config('app.disable_login', false)) {
            $user = User::where('email', 'admin@test.com')->first();
            if ($user) {
                Auth::login($user);
                return redirect()->route('dashboard')->with('success', 'Auto-login (Development Mode)');
            }
        }

        return view('auth.login');
    }

    /**
     * Memproses permintaan login.
     */
    public function login(Request $request)
    {
        // Bypass login untuk development jika DISABLE_LOGIN diaktifkan
        if (config('app.disable_login', false)) {
            $user = User::where('email', 'admin@test.com')->first();
            if ($user) {
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->intended('/dashboard')->with('success', 'Auto-login (Development Mode)');
            }
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // --- Perbaikan: Arahkan user berdasarkan role setelah login ---
            $user = Auth::user();
            if ($user->isAdmin()) {
                return redirect()->intended('/dashboard')->with('success', 'Login sebagai Admin berhasil! Selamat datang.');
            } else {
                return redirect()->intended('/dashboard')->with('success', 'Login berhasil! Selamat datang.');
            }
            // --- Akhir perbaikan ---
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Menampilkan form registrasi.
     */
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    /**
     * Memproses permintaan registrasi.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,user'], // <-- Tambahkan validasi role
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role, // <-- Simpan role dari input
        ]);

        Auth::attempt($request->only('email', 'password'));
        $request->session()->regenerate();

        return redirect('/dashboard')->with('success', 'Akun berhasil dibuat dan Anda telah login!');
    }

    /**
     * Memproses permintaan logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil logout.');
    }
}
