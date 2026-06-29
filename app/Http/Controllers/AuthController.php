<?php

namespace App\Http\Controllers;

use App\Models\EmployerProfile;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Redirige un utilisateur vers l'espace correspondant à son rôle. */
    public static function homeFor(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'employer' => route('employer.dashboard'),
            default => route('worker.dashboard'),
        };
    }

    // ---------- Connexion ----------
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Identifiant = email OU téléphone
        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (! Auth::attempt([$field => $data['login'], 'password' => $data['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['login' => 'Identifiants incorrects.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(self::homeFor(Auth::user()));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    // ---------- Inscription ----------
    public function showRegister()
    {
        return view('auth.onboarding');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:worker,employer'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_verified' => false,
        ]);
        $user->assignRole($data['role']);

        if ($data['role'] === 'worker') {
            WorkerProfile::create(['user_id' => $user->id, 'headline' => 'Nouveau profil']);
        } else {
            EmployerProfile::create(['user_id' => $user->id, 'type' => 'individual']);
        }

        // Génération + "envoi" du code OTP (en dev : log + flash). En prod : service SMS.
        $code = (string) random_int(100000, 999999);
        Cache::put("otp:{$user->id}", $code, now()->addMinutes(10));
        Log::info("OTP CyaoWork pour {$user->phone} : {$code}");

        $request->session()->put('otp_user', $user->id);

        return redirect()->route('otp.show')->with('dev_otp', $code);
    }

    // ---------- OTP ----------
    public function showOtp(Request $request)
    {
        if (! $request->session()->has('otp_user')) {
            return redirect()->route('register');
        }

        return view('auth.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => ['required', 'digits:6']]);

        $userId = $request->session()->get('otp_user');
        abort_unless($userId, 419);

        if (Cache::get("otp:{$userId}") !== $request->input('code')) {
            throw ValidationException::withMessages(['code' => 'Code invalide ou expiré.']);
        }

        $user = User::findOrFail($userId);
        $user->forceFill(['phone_verified_at' => now()])->save();

        Cache::forget("otp:{$userId}");
        $request->session()->forget('otp_user');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect(self::homeFor($user));
    }

    public function resendOtp(Request $request)
    {
        $userId = $request->session()->get('otp_user');
        abort_unless($userId, 419);

        $code = (string) random_int(100000, 999999);
        Cache::put("otp:{$userId}", $code, now()->addMinutes(10));
        Log::info("OTP CyaoWork (renvoi) user #{$userId} : {$code}");

        return back()->with('dev_otp', $code);
    }
}
