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

    // ==========================================
    // NUEVAS FUNCIONES PARA EL CLIENTE
    // ==========================================

    public function loginCliente(Request $request)
    {
        $request->validate([
            'dni' => 'required|string|max:11'
        ]);

        // Buscar si existe un usuario con ese DNI y que su rol sea 'cliente' usando el modelo Usuario
        $user = Usuario::where('dni', $request->dni)->where('rol', 'cliente')->first();

        if (!$user) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        // Generar el token de acceso de Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'usuario' => $user
        ]);
    }

    public function registroCliente(Request $request)
    {
        // Validamos que el DNI sea único en la tabla "usuarios" (no en "users")
        $request->validate([
            'dni' => 'required|string|max:11|unique:usuarios,dni',
            'nombre' => 'required|string|max:255'
        ]);

        // Creamos al cliente usando el modelo Usuario y la columna "nombre"
        $user = Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->dni . '@cliente.dtodo.com',
            'password' => bcrypt($request->dni), // Contraseña ficticia (no la usará)
            'rol' => 'cliente',
            'dni' => $request->dni,
            'activo' => 1 // Lo marcamos como activo por si tu sistema lo requiere
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'usuario' => $user
        ]);
    }
}