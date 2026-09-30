<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetallePedido;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Pantalla 4 (Alt. 2): Seguimiento de pedidos por estado (pendiente, en camino, recibido)
class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['proveedor', 'usuario']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $pedidos = $query->orderByDesc('fecha')->paginate($request->integer('per_page', 15));

        // Conteos por estado para las tarjetas de la Pantalla 4
        $resumen = Pedido::selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return response()->json([
            'pedidos' => $pedidos,
            'resumen_estados' => [
                'pendientes' => $resumen['pendiente'] ?? 0,
                'en_camino' => $resumen['en_camino'] ?? 0,
                'recibidos' => $resumen['recibido'] ?? 0,
                'cancelados' => $resumen['cancelado'] ?? 0,
            ],
        ]);
    }

    public function show(Pedido $pedido)
    {
        return response()->json($pedido->load(['proveedor', 'usuario', 'detalles.producto']));
    }

    // Pantalla: Registro de nuevo pedido a proveedor
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_proveedor' => 'required|exists:proveedores,id_proveedor',
            'fecha' => 'required|date',
            'fecha_estimada_entrega' => 'nullable|date|after_or_equal:fecha',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|exists:productos,id_producto',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pedido = DB::transaction(function () use ($request) {
            $total = collect($request->detalles)
                ->sum(fn ($d) => $d['cantidad'] * $d['precio_unitario']);

            $pedido = Pedido::create([
                'id_proveedor' => $request->id_proveedor,
                'id_usuario' => $request->user()->id_usuario,
                'fecha' => $request->fecha,
                'fecha_estimada_entrega' => $request->fecha_estimada_entrega,
                'estado' => 'pendiente',
                'total' => $total,
            ]);

            foreach ($request->detalles as $detalle) {
                DetallePedido::create([
                    'id_pedido' => $pedido->id_pedido,
                    'id_producto' => $detalle['id_producto'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                ]);
            }

            return $pedido;
        });

        return response()->json($pedido->load('detalles.producto'), 201);
    }

    // Cambiar estado del pedido: pendiente -> en_camino -> recibido / cancelado
    public function actualizarEstado(Request $request, Pedido $pedido)
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:pendiente,en_camino,recibido,cancelado',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $estadoAnterior = $pedido->estado;
        $nuevoEstado = $request->estado;

        DB::transaction(function () use ($pedido, $estadoAnterior, $nuevoEstado, $request) {
            $pedido->update(['estado' => $nuevoEstado]);

            // Al marcar como "recibido" se genera entrada automática de inventario (RF04)
            if ($nuevoEstado === 'recibido' && $estadoAnterior !== 'recibido') {
                foreach ($pedido->detalles as $detalle) {
                    $producto = Producto::find($detalle->id_producto);
                    $producto->stock += $detalle->cantidad;
                    $producto->save();

                    MovimientoInventario::create([
                        'id_producto' => $producto->id_producto,
                        'id_usuario' => $request->user()->id_usuario,
                        'tipo_movimiento' => 'entrada',
                        'cantidad' => $detalle->cantidad,
                        'motivo' => 'Recepción de pedido #' . $pedido->id_pedido,
                        'referencia_tipo' => 'pedido',
                        'referencia_id' => $pedido->id_pedido,
                        'fecha' => now(),
                    ]);
                }
            }
        });

        return response()->json($pedido->fresh()->load('detalles.producto'));
    }
}
