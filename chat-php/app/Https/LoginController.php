<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Contratante;
use App\Models\Prestador;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:4',
    ]);

    $credentials = $request->only('email', 'password');

    $guards = ['contratante', 'empresa', 'prestador'];

    foreach ($guards as $guard) {
        if ($accessToken = Auth::guard($guard)->attempt($credentials)) {

            // Gera refresh token manual (pode usar DB ou Redis)
            $refreshToken = base64_encode(Str::random(64));

            // Salva o refresh token associado ao userId no Redis/DB
            // Exemplo simples: tabela refresh_tokens
            \DB::table('refresh_tokens')->insert([
                'user_id' => Auth::guard($guard)->id(),
                'guard' => $guard,
                'refresh_token' => $refreshToken,
                'expires_at' => now()->addDays(7)
            ]);

            return response()->json([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'bearer',
                'expires_in' => Auth::factory()->getTTL() * 60, // 15 min
                'guard' => $guard,
                'user' => Auth::guard($guard)->user(),
            ]);
        }
    }

    return response()->json(['error' => 'Credenciais inválidas'], 401);
}
public function refresh(Request $request)
{
    $refreshToken = $request->input('refresh_token');

    $record = \DB::table('refresh_tokens')
        ->where('refresh_token', $refreshToken)
        ->where('expires_at', '>', now())
        ->first();

    if (!$record) {
        return response()->json(['error' => 'Refresh token inválido ou expirado'], 401);
    }

    // Gera novo access token
    $newAccessToken = Auth::guard($record->guard)->login(
        $record->guard === 'contratante'
            ? \app\Models\Contratante::find($record->user_id)
            : ($record->guard === 'empresa'
                ? \app\Models\Empresa::find($record->user_id)
                : \app\Models\Prestador::find($record->user_id))
    );

    return response()->json([
        'access_token' => $newAccessToken,
        'token_type' => 'bearer',
        'expires_in' => Auth::factory()->getTTL() * 60, // 15 min
    ]);
}

public function logout(Request $request)
{
    try {
        Auth::logout(); // invalida o access token atual
        $refreshToken = $request->input('refresh_token');

        if ($refreshToken) {
            \DB::table('refresh_tokens')->where('refresh_token', $refreshToken)->delete();
        }

        return response()->json(['message' => 'Logout realizado com sucesso']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao realizar logout'], 500);
    }
}



}