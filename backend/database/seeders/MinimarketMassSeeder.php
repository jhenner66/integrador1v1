<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MinimarketMassSeeder extends Seeder
{
    public function run(): void
    {
        $fecha = now();

        // 1. Categorías (5)
        $categorias = [
            ['id' => 1, 'nombre' => 'Abarrotes', 'descripcion' => 'Productos de primera necesidad', 'estado' => 1, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['id' => 2, 'nombre' => 'Bebidas y Refrescos', 'descripcion' => 'Gaseosas, jugos y aguas', 'estado' => 1, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['id' => 3, 'nombre' => 'Lácteos y Derivados', 'descripcion' => 'Leche, quesos y mantequilla', 'estado' => 1, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['id' => 4, 'nombre' => 'Limpieza del Hogar', 'descripcion' => 'Detergentes, lejías y escobas', 'estado' => 1, 'created_at' => $fecha, 'updated_at' => $fecha],
            ['id' => 5, 'nombre' => 'Golosinas y Snacks', 'descripcion' => 'Galletas, chocolates y papas fritas', 'estado' => 1, 'created_at' => $fecha, 'updated_at' => $fecha],
        ];
        DB::table('categorias')->insertOrIgnore($categorias);

        // 2. Proveedores (10)
        $proveedores = [];
        for ($i = 1; $i <= 10; $i++) {
            $proveedores[] = [
                'id' => $i,
                'nombre' => "Proveedor Distribuciones S.A.C. $i",
                'documento' => '20' . rand(100000000, 999999999),
                'telefono' => '9' . rand(10000000, 99999999),
                'email' => "contacto$i@proveedor.com",
                'direccion' => "Av. Industrial Mz. B Lote $i",
                'estado' => 1,
                'created_at' => $fecha,
                'updated_at' => $fecha
            ];
        }
        DB::table('proveedores')->insertOrIgnore($proveedores);

        // 3. Productos (60 registros)
        $productos = [];
        $nombresProds = ['Arroz', 'Azúcar', 'Aceite', 'Leche', 'Fideos', 'Atún', 'Galletas', 'Gaseosa', 'Detergente', 'Papel Higiénico'];
        for ($i = 1; $i <= 60; $i++) {
            $productos[] = [
                'id' => $i,
                'codigo' => 'PROD-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'nombre' => $nombresProds[array_rand($nombresProds)] . ' Marca Pro ' . $i,
                'categoria_id' => rand(1, 5),
                'proveedor_id' => rand(1, 10),
                'precio_compra' => rand(200, 1500) / 100,
                'precio_venta' => rand(1600, 3000) / 100,
                'stock' => rand(5, 100),
                'stock_minimo' => 10,
                'estado' => 1,
                'created_at' => $fecha,
                'updated_at' => $fecha
            ];
        }
        DB::table('productos')->insertOrIgnore($productos);

        // 4. Clientes (55 registros)
        $clientes = [];
        $nombresClientes = ['Carlos', 'Ana', 'Luis', 'Rosa', 'Jorge', 'Carmen', 'Miguel', 'Lucía', 'Pedro', 'Sofía'];
        $apellidosClientes = ['Pérez', 'Gómez', 'Rodríguez', 'Sánchez', 'Torres', 'Ramírez', 'Flores', 'Vásquez', 'Rojas', 'Mendoza'];
        for ($i = 1; $i <= 55; $i++) {
            $clientes[] = [
                'id' => $i,
                'tipo_documento' => 'DNI',
                'num_documento' => (string)rand(10000000, 99999999),
                'nombre' => $nombresClientes[array_rand($nombresClientes)] . ' ' . $apellidosClientes[array_rand($apellidosClientes)],
                'telefono' => '9' . rand(10000000, 99999999),
                'direccion' => 'Calle Los Pinos #' . rand(1, 500),
                'email' => "cliente$i@gmail.com",
                'estado' => 1,
                'created_at' => $fecha,
                'updated_at' => $fecha
            ];
        }
        DB::table('clientes')->insertOrIgnore($clientes);

        // 5. Movimientos de Inventario / Kardex (70 registros)
        $movimientos = [];
        for ($i = 1; $i <= 70; $i++) {
            $movimientos[] = [
                'id' => $i,
                'producto_id' => rand(1, 60),
                'id_usuario' => 1,
                'tipo' => (rand(0, 1) == 1) ? 'Entrada' : 'Salida',
                'cantidad' => rand(5, 30),
                'motivo' => 'Ajuste masivo de inventario nro ' . $i,
                'created_at' => $fecha,
                'updated_at' => $fecha
            ];
        }
        DB::table('movimientos_inventario')->insertOrIgnore($movimientos);

        // 6. Pedidos a Proveedores y sus Detalles (50 pedidos)
        for ($i = 1; $i <= 50; $i++) {
            $pedidoId = DB::table('pedidos')->insertGetId([
                'proveedor_id' => rand(1, 10),
                'id_usuario' => 1,
                'num_comprobante' => 'F00' . rand(1, 9) . '-' . rand(100000, 999999),
                'total' => rand(2000, 8000) / 10,
                'estado' => 'Recibido',
                'created_at' => $fecha,
                'updated_at' => $fecha
            ]);

            DB::table('detalle_pedidos')->insert([
                'pedido_id' => $pedidoId,
                'producto_id' => rand(1, 60),
                'cantidad' => rand(10, 50),
                'precio_compra' => rand(200, 1000) / 100,
                'created_at' => $fecha,
                'updated_at' => $fecha
            ]);
        }

        // 7. Ventas y Detalles de Ventas (65 ventas)
        for ($i = 1; $i <= 65; $i++) {
            $ventaId = DB::table('ventas')->insertGetId([
                'tipo_comprobante' => (rand(0, 1) == 1) ? 'Boleta' : 'Factura',
                'num_comprobante' => 'B00' . rand(1, 9) . '-' . rand(100000, 999999),
                'cliente_id' => rand(1, 55),
                'id_usuario' => 1,
                'impuesto' => rand(10, 50) / 10,
                'total' => rand(500, 4000) / 10,
                'estado' => 'Completada',
                'created_at' => $fecha,
                'updated_at' => $fecha
            ]);

            DB::table('detalle_ventas')->insert([
                'venta_id' => $ventaId,
                'producto_id' => rand(1, 60),
                'cantidad' => rand(1, 5),
                'precio' => rand(1500, 3000) / 100,
                'descuento' => 0.00,
                'created_at' => $fecha,
                'updated_at' => $fecha
            ]);
        }

        // 8. Alertas del Sistema (50 alertas)
        $alertas = [];
        for ($i = 1; $i <= 50; $i++) {
            $alertas[] = [
                'id' => $i,
                'tipo' => 'Stock Bajo',
                'mensaje' => 'Advertencia: El producto #' . rand(1, 60) . ' cuenta con stock crítico.',
                'leido' => rand(0, 1),
                'created_at' => $fecha,
                'updated_at' => $fecha
            ];
        }
        DB::table('alertas')->insertOrIgnore($alertas);
    }
}
