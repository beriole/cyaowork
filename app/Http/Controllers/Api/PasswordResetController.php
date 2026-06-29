<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /** POST /password/forgot — envoie un code SMS à 6 chiffres (valable 10 min). */
    public function forgot(Request $request)
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:30']]);

        $user = User::where('phone', $data['phone'])->first();
        $payload = ['message' => 'Si ce numéro existe, un code de réinitialisation a été envoyé par SMS.'];

        if ($user) {
            $code = (string) random_int(100000, 999999);
            Cache::put($this->key($data['phone']), Hash::make($code), now()->addMinutes(10));

            // TODO: brancher le vrai SMS (gateway) ici.
            if (config('app.debug')) {
                $payload['debug_code'] = $code; // visible uniquement en dev pour les tests
            }
        }

        return response()->json($payload);
    }

    /** POST /password/reset — vérifie le code et change le mot de passe. */
    public function reset(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'code' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $hash = Cache::get($this->key($data['phone']));
        if (! $hash || ! Hash::check($data['code'], $hash)) {
            throw ValidationException::withMessages(['code' => ['Code invalide ou expiré.']]);
        }

        $user = User::where('phone', $data['phone'])->firstOrFail();
        $user->update(['password' => Hash::make($data['password'])]);
        $user->tokens()->delete(); // déconnecte les sessions/tokens existants
        Cache::forget($this->key($data['phone']));

        return response()->json(['message' => 'Mot de passe réinitialisé. Vous pouvez vous connecter.']);
    }

    private function key(string $phone): string
    {
        return 'pwd_reset:'.preg_replace('/\D/', '', $phone);
    }
}
