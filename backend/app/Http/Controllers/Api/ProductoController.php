<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with('categoria')->where('activo', true);

        if ($request->filled('buscar')) {
            $texto = $request->buscar;
            $query->where(function ($q) use ($texto) {
                $q->where('nombre', 'like', "%{$texto}%")
                  ->orWhere('codigo', 'like', "%{$texto}%")
                  ->orWhere('codigo_barras', 'like', "%{$texto}%");
            });
        }

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->boolean('solo_stock_bajo')) {
            $query->stockBajo();
        }

        return response()->json(
            $query->orderBy('nombre')->paginate($request->integer('per_page', 20))
        );
    }

    public function buscarPorCodigoBarras(string $codigoBarras)
    {
        $producto = Producto::with('categoria')
            ->where(function ($q) use ($codigoBarras){
                $q->where('nombre','like',"%".$codigoBarras."%")
                ->orWhere('codigo', 'like', "%{$codigoBarras}%");
            })
            ->first();

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        return response()->json($producto);
    }

  public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'codigo' => 'required|string|max:50|unique:productos,codigo',
            'codigo_barras' => 'nullable|string|max:50|unique:productos,codigo_barras',
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string|max:255',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $producto = Producto::create($validator->validated());

        // REGISTRAR LA CREACIÓN COMO ENTRADA INICIAL EN EL HISTORIAL
        MovimientoInventario::create([
            'id_producto' => $producto->id_producto,
            'id_usuario' => $request->user() ? $request->user()->id_usuario : 1,
            'tipo_movimiento' => 'entrada',
            'cantidad' => $producto->stock,
            'motivo' => 'Creación de nuevo producto (Registro inicial)',
            'referencia_tipo' => 'creacion',
            'fecha' => now(),
        ]);

        $this->evaluarAlertaStock($producto);

        return response()->json($producto, 201);
    }

    public function destroy(Request $request, Producto $producto)
    {
        // REGISTRAR LA BAJA COMO SALIDA EN EL HISTORIAL ANTES DE DESACTIVARLO
        MovimientoInventario::create([
            'id_producto' => $producto->id_producto,
            'id_usuario' => $request->user() ? $request->user()->id_usuario : 1,
            'tipo_movimiento' => 'salida',
            'cantidad' => $producto->stock,
            'motivo' => 'Producto dado de baja (' . $producto->nombre . ')',
            'referencia_tipo' => 'eliminacion',
            'fecha' => now(),
        ]);

        $producto->update(['activo' => false]);

        return response()->json(['message' => 'Producto dado de baja correctamente.']);
    }
    
    public function registrarMovimiento(Request $request, Producto $producto)
    {
        $validator = Validator::make($request->all(), [
            'tipo_movimiento' => 'required|in:entrada,salida',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->tipo_movimiento === 'salida' && $producto->stock < $request->cantidad) {
            return response()->json(['message' => 'Stock insuficiente para la salida solicitada.'], 422);
        }

        $movimiento = MovimientoInventario::create([
            'id_producto' => $producto->id_producto,
            'id_usuario' => $request->user() ? $request->user()->id_usuario : 1,
            'tipo_movimiento' => $request->tipo_movimiento,
            'cantidad' => $request->cantidad,
            'motivo' => $request->motivo,
            'referencia_tipo' => 'ajuste',
            'fecha' => now(),
        ]);

        $producto->stock += $request->tipo_movimiento === 'entrada' ? $request->cantidad : -$request->cantidad;
        $producto->save();

        $this->evaluarAlertaStock($producto);

        return response()->json([
            'movimiento' => $movimiento,
            'producto' => $producto->fresh(),
        ], 201);
    }

    private function evaluarAlertaStock(Producto $producto): void
    {
        $producto->refresh();

        $alertaExistente = Alerta::where('id_producto', $producto->id_producto)
            ->where('tipo', 'stock_bajo')
            ->where('atendida', false)
            ->first();

        if ($producto->stock <= $producto->stock_minimo) {
            if (!$alertaExistente) {
                Alerta::create([
                    'id_producto' => $producto->id_producto,
                    'tipo' => 'stock_bajo',
                    'mensaje' => "Stock bajo: {$producto->nombre} tiene {$producto->stock} unidades (mínimo {$producto->stock_minimo}).",
                    'atendida' => false,
                ]);
            }
        } elseif ($alertaExistente) {
            $alertaExistente->update(['atendida' => true]);
        }
    }

    public function historialMovimientos()
    {
        $movimientos = MovimientoInventario::orderBy('created_at', 'desc')
            ->take(100)
            ->get()
            ->map(function($mov) {
                $producto = Producto::find($mov->id_producto);
                $mov->producto = $producto ? ['nombre' => $producto->nombre] : null;
                return $mov;
            });
            
        return response()->json($movimientos);
    }
}