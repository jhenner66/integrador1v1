<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// RF11: Dashboard - Mostrar indicadores clave (Pantalla 2, Alternativa 2)
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $productosStockBajo = Producto::stockBajo()->where('activo', true)->count();

        $ventasDelMes = Venta::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('total');

        $ventasMesAnterior = Venta::whereMonth('fecha', now()->subMonth()->month)
            ->whereYear('fecha', now()->subMonth()->year)
            ->sum('total');

        $variacion = $ventasMesAnterior > 0
            ? round((($ventasDelMes - $ventasMesAnterior) / $ventasMesAnterior) * 100, 1)
            : null;

        $ventasUltimos7Dias = Venta::selectRaw('DATE(fecha) as dia, SUM(total) as total')
            ->where('fecha', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $rotacionInventario = DB::table('detalle_ventas')
            ->join('productos', 'productos.id_producto', '=', 'detalle_ventas.id_producto')
            ->selectRaw('productos.id_producto, productos.nombre, SUM(detalle_ventas.cantidad) as unidades_vendidas')
            ->groupBy('productos.id_producto', 'productos.nombre')
            ->orderByDesc('unidades_vendidas')
            ->limit(5)
            ->get();

        $productosStockBajoLista = Producto::stockBajo()
            ->where('activo', true)
            ->select('id_producto', 'nombre', 'stock', 'stock_minimo')
            ->limit(10)
            ->get();

        $pedidosPendientes = Pedido::where('estado', 'pendiente')->count();

        return response()->json([
            'alertas_stock_bajo' => $productosStockBajo,
            'ventas_del_mes' => round($ventasDelMes, 2),
            'variacion_ventas_pct' => $variacion,
            'ventas_ultimos_7_dias' => $ventasUltimos7Dias,
            'rotacion_inventario_top5' => $rotacionInventario,
            'productos_stock_bajo' => $productosStockBajoLista,
            'pedidos_pendientes' => $pedidosPendientes,
        ]);
    }
}
