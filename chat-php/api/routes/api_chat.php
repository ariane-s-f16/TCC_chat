<?php
//Recebe email e password pela rota /login.
//Busca o usuário no banco via \App\Models\User.
//Verifica a senha com Hash::check.
//Seestiver tudo certo → gera um token JWT 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


// Exemplo de login para gerar token
Route::post('/login', function(Request $request){
    // Aqui você valida login/email/senha
    $email = $request->input('email');
    $password = $request->input('password');

    $user = \App\Models\User::where('email', $email)->first();
    if(!$user || !\Hash::check($password, $user->password)){
        return response()->json(['error'=>'Credenciais inválidas'], 401);
    }
    $accessToken = JWT::encode($accessPayload, env('JWT_SECRET'), 'HS256');

    $refreshPayload = [
        'userId' => $user->id,
        'exp' => time() + (7 * 24 * 60 * 60) // 7 dias
    ];
    $refreshToken = JWT::encode($refreshPayload, env('JWT_SECRET'), 'HS256');

    return response()->json([
        'access_token' => $accessToken,
        'refresh_token' => $refreshToken
    ]);
});
Route::post('/refresh', function(Request $request){
        try {
            $decoded = JWT::decode($request->refresh_token, new Key(env('JWT_SECRET'), 'HS256'));
            $newAccess = [
                'userId' => $decoded->userId,
                'exp' => time() + 900 // 15 min
            ];
            $newAccessToken = JWT::encode($newAccess, env('JWT_SECRET'), 'HS256');
            return response()->json(['access_token' => $newAccessToken]);
        } catch (\Exception $e) {
            return response()->json(['error'=>'Refresh token inválido'], 401);
        }
    });


    // Gerar token JWT com expiração de 1 hora
    $payload = [
        'userId' => $user->id,
        'exp' => time() + 3600
    ];

    $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

    return response()->json(['token'=>$token]);


// Valida token enviado pelo WebSocket
Route::post('/validate-token', function(Request $request){
    $token = $request->input('token');

    try {
        $decoded = JWT::decode($token, new \Firebase\JWT\Key(env('JWT_SECRET'), 'HS256'));
        return response()->json(['userId' => $decoded->userId]);
    } catch (\Exception $e){
        return response()->json(['error'=>'Token inválido'], 401);
    }
});
