<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HistorialUsuario;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Pantalla 1 (Alternativa 2): Inicio de sesión con selección de rol
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'rol' => 'nullable|in:administrador,vendedor,almacen',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        if (!$usuario->activo) {
            return response()->json(['message' => 'Usuario deshabilitado. Contacte al administrador.'], 403);
        }

        // Si el frontend envía el rol esperado, se valida que coincida (selector de rol en login)
        if ($request->filled('rol') && $usuario->rol !== $request->rol) {
            return response()->json(['message' => 'El rol seleccionado no coincide con el usuario.'], 422);
        }

        $token = $usuario->createToken('dtodo-token')->plainTextToken;

        HistorialUsuario::create([
            'id_usuario' => $usuario->id_usuario,
            'accion' => 'Inicio de sesión',
            'detalle' => 'Login exitoso desde ' . $request->ip(),
            'fecha' => now(),
        ]);

        return response()->json([
            'token' => $token,
            'usuario' => $usuario,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
