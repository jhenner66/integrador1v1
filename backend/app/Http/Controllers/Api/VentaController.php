<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\DetalleVenta;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $query = Venta::with('usuario');

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha', '<=', $request->fecha_fin);
        }

        return response()->json(
            $query->orderByDesc('fecha')->paginate($request->integer('per_page', 15))
        );
    }

    public function show(Venta $venta)
    {
        return response()->json($venta->load('usuario', 'detalles.producto'));
    }

    // RF05: registro de salidas de productos (ventas), descuenta stock automáticamente
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|exists:productos,id_producto',
            'detalles.*.cantidad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $venta = DB::transaction(function () use ($request) {
                $total = 0;
                $detallesValidados = [];

                foreach ($request->detalles as $item) {
                    $producto = Producto::findOrFail($item['id_producto']);

                    if ($producto->stock < $item['cantidad']) {
                        throw new \RuntimeException("Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}.");
                    }

                    $subtotal = $producto->precio * $item['cantidad'];
                    $total += $subtotal;

                    $detallesValidados[] = [
                        'producto' => $producto,
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $producto->precio,
                    ];
                }

                $venta = Venta::create([
                    'id_usuario' => $request->user()->id_usuario,
                    'fecha' => now(),
                    'total' => $total,
                    'comprobante' => 'BOL-' . str_pad((string) (Venta::max('id_venta') + 1), 6, '0', STR_PAD_LEFT),
                ]);

                foreach ($detallesValidados as $d) {
                    DetalleVenta::create([
                        'id_venta' => $venta->id_venta,
                        'id_producto' => $d['producto']->id_producto,
                        'cantidad' => $d['cantidad'],
                        'precio_unitario' => $d['precio_unitario'],
                    ]);

                    $d['producto']->stock -= $d['cantidad'];
                    $d['producto']->save();

                    MovimientoInventario::create([
                        'id_producto' => $d['producto']->id_producto,
                        'id_usuario' => $request->user()->id_usuario,
                        'tipo_movimiento' => 'salida',
                        'cantidad' => $d['cantidad'],
                        'motivo' => 'Venta #' . $venta->id_venta,
                        'referencia_tipo' => 'venta',
                        'referencia_id' => $venta->id_venta,
                        'fecha' => now(),
                    ]);

                    // RF08: recalcular alerta de stock bajo tras la venta
                    $d['producto']->refresh();
                    if ($d['producto']->stock <= $d['producto']->stock_minimo) {
                        Alerta::firstOrCreate(
                            [
                                'id_producto' => $d['producto']->id_producto,
                                'tipo' => 'stock_bajo',
                                'atendida' => false,
                            ],
                            [
                                'mensaje' => "Stock bajo: {$d['producto']->nombre} tiene {$d['producto']->stock} unidades (mínimo {$d['producto']->stock_minimo}).",
                            ]
                        );
                    }
                }

                return $venta;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($venta->load('detalles.producto'), 201);
    }

    // RF10: Reporte de ventas por rango de fechas (Pantalla 5, Alt. 2)
    public function reporte(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subDays(6)->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());

        $ventasPorDia = Venta::selectRaw('DATE(fecha) as dia, COUNT(*) as num_ventas, SUM(total) as total')
            ->whereDate('fecha', '>=', $fechaInicio)
            ->whereDate('fecha', '<=', $fechaFin)
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $totalGeneral = $ventasPorDia->sum('total');

        return response()->json([
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'ventas_por_dia' => $ventasPorDia,
            'total_general' => round($totalGeneral, 2),
        ]);
    }

    // Exportación a Excel (RF10: reportes exportables en PDF/Excel)
    public function exportarExcel(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->subDays(30)->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());

        $ventas = Venta::with('detalles.producto')
            ->whereDate('fecha', '>=', $fechaInicio)
            ->whereDate('fecha', '<=', $fechaFin)
            ->orderBy('fecha')
            ->get();

        $filename = 'reporte_ventas_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($ventas) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Comprobante', 'Fecha', 'Usuario', 'Producto', 'Cantidad', 'Precio Unit.', 'Subtotal']);

            foreach ($ventas as $venta) {
                foreach ($venta->detalles as $detalle) {
                    fputcsv($handle, [
                        $venta->comprobante,
                        $venta->fecha,
                        $venta->usuario->nombre ?? '',
                        $detalle->producto->nombre ?? '',
                        $detalle->cantidad,
                        $detalle->precio_unitario,
                        $detalle->subtotal,
                    ]);
                }
            }

            fclose($handle);
        };

        // NOTA: para exportar a PDF real, instalar barryvdh/laravel-dompdf
        // y generar la vista con Pdf::loadView('reportes.ventas', compact('ventas'))->download(...)
        return response()->stream($callback, 200, $headers);
    }
}
