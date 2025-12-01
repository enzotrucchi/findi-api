<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function googleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $idToken = $request->input('id_token');

        // 1) Verificar el token contra Google
        $payload = $this->verifyGoogleToken($idToken);

        // 2) Verificar email verificado
        if (($payload['email_verified'] ?? 'false') !== 'true') {
            throw ValidationException::withMessages([
                'email' => ['El email de Google no está verificado.'],
            ]);
        }

        // 3) Buscar o crear usuario local
        $user = User::updateOrCreate(
            ['email' => $payload['email']],
            [
                'name' => $payload['name'] ?? $payload['email'],
                'email_verified_at' => now(),
            ]
        );

        // 4) Crear token de acceso (Sanctum)
        $token = $user->createToken('findi-pwa')->plainTextToken;

        // 5) Responder al front
        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Verifica el id_token con Google usando el endpoint tokeninfo.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function verifyGoogleToken(string $idToken): array
    {
        $clientId = config('services.google.client_id');

        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if (! $response->ok()) {
            throw ValidationException::withMessages([
                'id_token' => ['No se pudo validar el token de Google.'],
            ]);
        }

        $data = $response->json();

        if (! isset($data['aud']) || $data['aud'] !== $clientId) {
            throw ValidationException::withMessages([
                'id_token' => ['El token de Google no corresponde a este cliente.'],
            ]);
        }

        return $data;
    }
}
