<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Producto;
use Illuminate\Http\Request;

// RF08: alertas automáticas de stock bajo | RF09: alertas de productos próximos a vencer
class AlertaController extends Controller
{
    public function index(Request $request)
    {
        $alertas = Alerta::with('producto')
            ->when(!$request->boolean('incluir_atendidas'), fn ($q) => $q->where('atendida', false))
            ->orderByDesc('created_at')
            ->get();

        $proximosAVencer = Producto::proximosAVencer(30)
            ->where('activo', true)
            ->select('id_producto', 'nombre', 'stock', 'fecha_vencimiento')
            ->orderBy('fecha_vencimiento')
            ->get();

        return response()->json([
            'alertas' => $alertas,
            'productos_proximos_a_vencer' => $proximosAVencer,
        ]);
    }

    public function marcarAtendida(Alerta $alerta)
    {
        $alerta->update(['atendida' => true]);

        return response()->json($alerta);
    }
}
